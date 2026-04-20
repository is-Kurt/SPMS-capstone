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

        $fabricator->setOverrides([
            'email'      => $faker->unique()->lexify('???@test.com'),
            'password'   => $password,
            'department' => $faker->randomElement($departments),
            'position'   => $faker->randomElement($positions),
            'role'       => 'admin',
        ]);
        $fabricator->create(1);

        for ($i = 0; $i < 5; $i++) {
            $fabricator->setOverrides([
                'email'      => $faker->unique()->lexify('???@test.com'),
                'password'   => $password,
                'department' => $faker->randomElement($departments),
                'position'   => $faker->randomElement($positions),
                'role'       => 'supervisor',
            ]);
            $fabricator->create(1);
        }

        for ($i = 0; $i < 20; $i++) {
            $fabricator->setOverrides([
                'email'      => $faker->unique()->lexify('???@test.com'),
                'password'   => $password,
                'department' => $faker->randomElement($departments),
                'position'   => $faker->randomElement($positions),
                'role'       => 'user',
            ]);
            $fabricator->create(1);
        }
    }
}