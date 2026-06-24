<?php

use App\Enums\EmailStatus;
use App\Models\EmailQueueModel;

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
        $processed++;
    }

    $remaining = $queueModel->where('status', EmailStatus::PENDING->value)->countAllResults();

    return [
        'processed' => $processed,
        'remaining' => $remaining
    ];
}