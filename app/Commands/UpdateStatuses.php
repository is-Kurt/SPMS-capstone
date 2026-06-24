<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class UpdateStatuses extends BaseCommand
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
    protected $name = 'spms:update-statuses';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Sweeps the database to update IPCR/DPCR/OPCR statuses based on evaluation dates.';

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
        CLI::write('Sweeping database for expired evaluation dates...', 'yellow');

        try {
            $documentModel = new \App\Models\DocumentFolderModel();
            $documentModel->updateTimeBasedStatuses();
            
            CLI::write('Successfully updated all document statuses!', 'green');

            // ==========================================
            // PROCESS THE EMAIL QUEUE
            // ==========================================
            CLI::write('Checking for pending automated emails...', 'yellow');
            
            helper('email_queue');
            $result = \process_email_queue(0);
            
            if ($result['processed'] > 0) {
                CLI::write("Successfully sent {$result['processed']} automated emails.", 'green');
            } else {
                CLI::write("No pending emails to send.", 'cyan');
            }

        } catch (\Exception $e) {
            CLI::error('Failed to run 1-minute tasks: ' . $e->getMessage());
        }
    }
}
