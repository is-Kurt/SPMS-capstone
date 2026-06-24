<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Enums\FolderStatus;
use App\Enums\EmailStatus;

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
        $db = \Config\Database::connect();
        $threeDaysFromNow = date('Y-m-d', strtotime('+3 days'));

        // ==========================================
        // NEARING DEADLINE (3 Days Left)
        // ==========================================
        $nearingFolders = $db->table('document_folders df')
            ->select('df.id, u.email, u.first_name, df.eval_date_start')
            ->join('users u', 'u.id = df.user_id')
            ->where('df.status', FolderStatus::DRAFT->value)
            ->where('DATE(df.eval_date_start)', $threeDaysFromNow) 
            ->get()->getResultArray();

        foreach ($nearingFolders as $folder) {
            $link = site_url("folders/" . $folder['id']);
            
            $db->table('email_queue')->insert([
                'to_email'   => $folder['email'],
                'subject'    => 'Action Required: Evaluation Submission Deadline Approaching',
                'body'       => "Hello {$folder['first_name']},<br><br>This is an automated reminder that your performance evaluation submission is due in 3 days on <b>{$folder['eval_date_start']}</b>. Please finalize and submit your self-rating before the system locks your folder.<br><br><a href='{$link}'>Click here to open your evaluation folder</a>",
                'status'     => EmailStatus::PENDING->value,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        CLI::write("Queued " . count($nearingFolders) . " 'Nearing Deadline' reminders.", 'green');
        CLI::write("Dispatching automated alerts...", 'yellow');
        
        helper('email_queue');
        $result = process_email_queue(0); 
        
        CLI::write("Successfully sent {$result['processed']} automated emails.", 'green');
    }
}
