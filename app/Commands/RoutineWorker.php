<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\DocumentFolderModel;

class RoutineWorker extends BaseCommand
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
    protected $name = 'spms:routine';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Runs 1-minute interval background tasks like updating statuses and garbage collection.';

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
            $result = \process_email_queue(30);
            
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
               
            // 4. WIPE EXPIRED 2FA CODES (Security Sweeper)
            $db->table('users')
               ->where('two_factor_expires_at <', $now)
               ->where('two_factor_code IS NOT NULL')
               ->update([
                   'two_factor_code' => null, 
                   'two_factor_expires_at' => null
               ]);

            CLI::write("Garbage Collection complete. Cleared old records.", 'green');

        } catch (\Exception $e) {
            CLI::error('Failed to run 1-minute tasks: ' . $e->getMessage());
        }
    }
}
