<?php
define('FCPATH', '/var/www/html/SPMS-capstone/public/');
chdir('/var/www/html/SPMS-capstone');
require 'public/index.php';
$email = \Config\Services::email();
$email->setTo('skurto123o@gmail.com');
$email->setSubject('Test');
$email->setMessage('Test Body');
if (!$email->send()) {
    echo $email->printDebugger(['headers']);
} else {
    echo 'Sent OK';
}
