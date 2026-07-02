<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

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
