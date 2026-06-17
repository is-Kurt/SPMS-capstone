<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Organization extends BaseConfig
{
    public array $roles = [
        'Employee',
        'Head of Office',
        'Director',
        'Dean',
        'Vice President',
        'Campus Administrator',
        'Admin',
    ];

    public array $positions = [
        'Instructor 1',
        'Instructor 2',
        'Instructor 3',
        'Assistant Professor',
        'Associate Professor',
        'Support Staff'
    ];

    public array $departments = [
        'HR'             => 'Human Resources (HR)',
        'ICT'            => 'Information & Comms Tech (ICT)',
        'Academics'      => 'Academics',
        'Finance'        => 'Finance & Accounting',
        'Administration' => 'Administration'
    ];
}
