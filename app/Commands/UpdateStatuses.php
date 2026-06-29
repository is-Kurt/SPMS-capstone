<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\DocumentFolderModel;

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
            $documentModel = new DocumentFolderModel();
            $documentModel->updateTimeBasedStatuses();
            
            CLI::write('Successfully updated all document statuses!', 'green');

            CLI::write('Checking for pending automated emails...', 'yellow');
            
            helper('email_queue');
            $result = \process_email_queue(0);
            
            if ($result['processed'] > 0) {
                CLI::write("Successfully sent {$result['processed']} automated emails.", 'green');
            } else {
                CLI::write("No pending emails to send.", 'cyan');
            }

            // ==========================================
            // GARBAGE COLLECTION (CLEANUP)
            // ==========================================
            CLI::write('Running garbage collection...', 'yellow');
            $db = \Config\Database::connect();
            $now = date('Y-m-d H:i:s');
            
            // 1. Delete Expired/Abandoned Invitations
            $deletedInvites = $db->table('invitations')
                                 ->where('expires_at <', $now)
                                 ->where('status', \App\Enums\InvitationStatus::PENDING->value)
                                 ->delete();
            
            // 2. Delete Sent Emails older than 30 days to save space
            $thirtyDaysAgo = date('Y-m-d H:i:s', strtotime('-30 days'));
            $deletedEmails = $db->table('email_queue')
                                ->where('status', 'sent')
                                ->where('created_at <', $thirtyDaysAgo)
                                ->delete();

            // 3. WIPE EXPIRED PASSWORD RESET CODES (Security Sweeper)
            $db->table('users')
               ->where('reset_code_expires_at <', $now)
               ->where('reset_code IS NOT NULL')
               ->update([
                   'reset_code' => null, 
                   'reset_code_expires_at' => null
               ]);

            CLI::write("Garbage Collection complete. Cleared old records.", 'green');

        } catch (\Exception $e) {
            CLI::error('Failed to run 1-minute tasks: ' . $e->getMessage());
        }
    }
}
