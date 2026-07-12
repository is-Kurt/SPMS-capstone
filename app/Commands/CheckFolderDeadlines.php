<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

use App\Models\DocumentFolderModel;

class CheckFolderDeadlines extends BaseCommand
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
    protected $name = 'folders:check-deadlines';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Checks for nearing and missed IPCR submission deadlines.';

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

        $nearingFolders = $folderModel->getNearingDeadlineFolders(3);

        foreach ($nearingFolders as $folder) {
            $link = site_url("folders/" . $folder['id']);

            queue_email(
                $folder['email'],
                'Action Required: Evaluation Submission Deadline Approaching',
                render_email('deadline_approaching', [
                    'firstName' => $folder['first_name'],
                    'deadline'  => date('F j, Y', strtotime($folder['eval_date_end'])),
                    'link'      => $link,
                ])
            );

            // Mark it reminded immediately (not batched at the end) so a folder
            // never gets queued twice even if something interrupts this loop partway.
            $folderModel->update($folder['id'], ['deadline_reminder_sent_at' => date('Y-m-d H:i:s')]);
        }

        CLI::write("Queued " . count($nearingFolders) . " 'Nearing Deadline' reminders.", 'green');
        CLI::write("Dispatching automated alerts...", 'yellow');

        $result = \process_email_queue(0);

        CLI::write("Successfully sent {$result['processed']} automated emails.", 'green');
    }
}
