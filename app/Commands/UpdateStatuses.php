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
    protected $usage = 'spms:update-statuses';

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
            // Call the math logic we put in the model!
            $documentModel = new \App\Models\DocumentFolderModel();
            $documentModel->updateTimeBasedStatuses();
            
            CLI::write('Successfully updated all document statuses!', 'green');
        } catch (\Exception $e) {
            CLI::error('Failed to update statuses: ' . $e->getMessage());
        }
    }
}
