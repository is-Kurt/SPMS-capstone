<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Test\Fabricator;
use App\Models\UserModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $model = new UserModel();
        $fabricator = new Fabricator($model);

        $fabricator->setFormatters([
            'first_name' => 'firstName',
            'last_name'  => 'lastName',
        ]);

        $faker = $fabricator->getFaker();
        
        $password = password_hash('123', PASSWORD_DEFAULT);
        
        $departments = ['HR', 'ICT', 'Academics', 'Finance', 'Administration'];
        $positions = ['Instructor 1', 'Instructor 2', 'Instructor 3', 'Assistant Professor', 'Associate Professor', 'Staff'];
        
        $opcrRoles = ['Vice President', 'Campus Administrator'];
        $dpcrRoles = ['Dean', 'Director', 'Head of Office'];

        // 1. Seed Admin (No Dept, No Position)
        $fabricator->setOverrides([
            'email'      => 'admin@test.com',
            'password'   => $password,
            'role'       => 'Admin',
            'department' => null,
            'position'   => null,
        ]);
        $fabricator->create(1);

        // 2. Seed OPCR Users (No Dept, No Position)
        for ($i = 0; $i < 2; $i++) {
            $fabricator->setOverrides([
                'email'      => $faker->unique()->lexify('???@test.com'),
                'password'   => $password,
                'role'       => $faker->randomElement($opcrRoles),
                'department' => null,
                'position'   => null,
            ]);
            $fabricator->create(1);
        }

        // 3. Seed DPCR Users (Has Dept, No Position)
        for ($i = 0; $i < 5; $i++) {
            $fabricator->setOverrides([
                'email'      => $faker->unique()->lexify('???@test.com'),
                'password'   => $password,
                'role'       => $faker->randomElement($dpcrRoles),
                'department' => $faker->randomElement($departments),
                'position'   => null,
            ]);
            $fabricator->create(1);
        }

        // 4. Seed IPCR Employees (Has Dept, Has Position)
        for ($i = 0; $i < 20; $i++) {
            $fabricator->setOverrides([
                'email'      => $faker->unique()->lexify('???@test.com'),
                'password'   => $password,
                'role'       => 'Employee',
                'department' => $faker->randomElement($departments),
                'position'   => $faker->randomElement($positions),
            ]);
            $fabricator->create(1);
        }
    }
}