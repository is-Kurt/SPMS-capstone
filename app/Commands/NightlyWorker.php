<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

use App\Models\DocumentFolderModel;

class NightlyWorker extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'SPMS Tasks';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'spms:nightly';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Runs daily background tasks like checking deadlines.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        helper('email_queue');
        $folderModel = new DocumentFolderModel();

        // 1. Evaluation Submission Deadlines
        $nearingEvalFolders = $folderModel->getNearingDeadlineFolders(3);

        foreach ($nearingEvalFolders as $folder) {
            $link = site_url("folders/" . $folder['id']);
            $docType = strtolower($folder['doc_type'] ?? 'ipcr');
            $evalDateEndKey = $docType . '_eval_end';
            $deadline = $folder[$evalDateEndKey] ?? null;
            if (!$deadline) continue;

            queue_email(
                $folder['email'],
                'Action Required: Evaluation Submission Deadline Approaching',
                render_email('deadline_approaching', [
                    'firstName' => $folder['first_name'],
                    'deadline'  => date('F j, Y', strtotime($deadline)),
                    'link'      => $link,
                ])
            );

            $folderModel->update($folder['id'], ['deadline_reminder_sent_at' => date('Y-m-d H:i:s')]);
        }

        // 2. Target Setting Deadlines
        $nearingTargetFolders = $folderModel->getNearingTargetDeadlineFolders(3);

        foreach ($nearingTargetFolders as $folder) {
            $link = site_url("folders/" . $folder['id']);
            $docType = strtolower($folder['doc_type'] ?? 'ipcr');
            $targetDateEndKey = $docType . '_target_end';
            $deadline = $folder[$targetDateEndKey] ?? null;
            if (!$deadline) continue;

            queue_email(
                $folder['email'],
                'Action Required: Target Setting Deadline Approaching',
                render_email('target_deadline_approaching', [
                    'firstName' => $folder['first_name'],
                    'deadline'  => date('F j, Y', strtotime($deadline)),
                    'link'      => $link,
                ])
            );

            $folderModel->update($folder['id'], ['target_deadline_reminder_sent_at' => date('Y-m-d H:i:s')]);
        }

        CLI::write("Queued " . count($nearingEvalFolders) . " 'Nearing Eval Deadline' reminders.", 'green');
        CLI::write("Queued " . count($nearingTargetFolders) . " 'Nearing Target Deadline' reminders.", 'green');
        CLI::write("Dispatching automated alerts...", 'yellow');

        $result = \process_email_queue(30);

        CLI::write("Successfully sent {$result['processed']} automated emails.", 'green');
    }
}
