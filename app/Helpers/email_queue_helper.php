<?php

use App\Enums\EmailStatus;
use App\Models\EmailQueueModel;

/**
 * Renders one of the app/Views/emails/*.php content templates into the shared
 * branded layout (app/Views/emails/layout.php) and returns the final HTML
 * string ready to pass as queue_email()'s $body - keeps email markup out of
 * controllers/models entirely.
 */
function render_email(string $template, array $data = []): string
{
    return view('emails/layout', [
        'content' => view("emails/{$template}", $data),
    ]);
}

/**
 * Instantly queues an email to be sent in the background.
 */
function queue_email(string $toEmail, string $subject, string $body)
{
    $queueModel = new EmailQueueModel();
    return $queueModel->insert([
        'to_email'   => $toEmail,
        'subject'    => $subject,
        'body'       => $body,
        'status'     => EmailStatus::PENDING->value,
        'created_at' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Sends $response to the browser right away, then drains up to $limit queued
 * emails before the process ends - for actions a user is actively waiting on
 * (invite sent, password reset code, etc.) so they don't sit stuck for up to
 * a minute waiting on the spms:update-statuses cron sweep to pick it up.
 *
 * On Nginx+PHP-FPM production this detaches the browser via
 * fastcgi_finish_request() first, so sending mail adds zero latency to the
 * response. Locally (or anywhere fastcgi_finish_request isn't available)
 * it just falls back to sending inline before returning normally.
 */
function dispatch_email_now($response, int $limit = 1)
{
    if (ENVIRONMENT === 'development' || !function_exists('fastcgi_finish_request')) {
        process_email_queue($limit);
        return $response;
    }

    $response->send();
    session_write_close();
    fastcgi_finish_request();
    process_email_queue($limit);
    exit();
}

/**
 * Processes pending emails. Pass 0 to process all (for midnight tasks),
 * or a number to process in batches (for UI/AJAX).
 */
function process_email_queue(int $limit = 5): array
{
    $queueModel = new EmailQueueModel();
    $emailService = \Config\Services::email();

    // Build the query
    $builder = $queueModel->where('status', EmailStatus::PENDING->value);
    if ($limit > 0) {
        $builder->limit($limit);
    }
    $emails = $builder->findAll();

    if (empty($emails)) {
        return ['processed' => 0, 'remaining' => 0];
    }

    $processed = 0;

    foreach ($emails as $job) {
        // Atomically claim this row before sending - if another concurrent call (a
        // second browser tab's poller, an overlapping cron run) already grabbed it
        // between our SELECT above and this UPDATE, affectedRows() comes back 0 and
        // we skip it, instead of two processes both sending the same email.
        $queueModel->where('id', $job['id'])
                   ->where('status', EmailStatus::PENDING->value)
                   ->set(['status' => EmailStatus::SENDING->value])
                   ->update();

        if ($queueModel->db->affectedRows() !== 1) {
            continue;
        }

        try {
            $emailService->clear(); // CRITICAL: Prevents recipient stacking in loops

            $emailService->setTo($job['to_email']);
            $emailService->setSubject($job['subject']);
            $emailService->setMessage($job['body']);

            if ($emailService->send()) {
                $queueModel->update($job['id'], ['status' => EmailStatus::SENT->value]);
            } else {
                $queueModel->update($job['id'], [
                    'status'   => EmailStatus::FAILED->value,
                    'attempts' => $job['attempts'] + 1
                ]);
            }
        } catch (\Throwable $e) {
            // Make sure a thrown error (e.g. an SMTP failure) still releases the claim
            // instead of leaving the row stuck at "sending" forever.
            $queueModel->update($job['id'], [
                'status'   => EmailStatus::FAILED->value,
                'attempts' => $job['attempts'] + 1
            ]);
        }
        $processed++;
    }

    $remaining = $queueModel->where('status', EmailStatus::PENDING->value)->countAllResults();

    return [
        'processed' => $processed,
        'remaining' => $remaining
    ];
}