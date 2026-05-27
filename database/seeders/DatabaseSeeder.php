<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Item;
use App\Models\Location;
use App\Models\Camera;
use App\Models\Detection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // USER ADMIN
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin APD',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $this->call([
            ItemsSeeder::class,
            LocationsSeeder::class,
            CamerasSeeder::class,
            DetectionsSeeder::class,
        ]);
    }
}