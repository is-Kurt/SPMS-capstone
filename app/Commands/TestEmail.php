<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestEmail extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:email';
    protected $description = 'Test email send';

    public function run(array $params)
    {
        $email = \Config\Services::email();
        $email->setTo('skurto123o@gmail.com');
        $email->setSubject('Test');
        $email->setMessage('Test Body');
        if (!$email->send()) {
            CLI::error($email->printDebugger(['headers']));
        } else {
            CLI::write('Sent OK');
        }
    }
}
