<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usersAdminApd = [
            [
                'name' => 'Admin APD',
                'email' => 'admin@admin.com',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Admin Gmail APD',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('password'),
            ],
        ];
        foreach ($usersAdminApd as $user) {
            $userCreated = User::create($user);
            $userCreated->assignRole('admin_apd');
        }
    }
}
