<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Test\Fabricator;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        $model = new User();
        $fabricator = new Fabricator($model);

        $fabricator->setUnique('email');
        $fabricator->setUnique('username');
        $fabricator->setOverrides([
            'password' => password_hash('12345678', PASSWORD_DEFAULT)
        ]);

        $fabricator->create(1);
    }
}