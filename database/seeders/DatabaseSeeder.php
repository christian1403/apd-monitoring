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
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
            UsersSeeder::class,
            ItemsSeeder::class,
            LocationsSeeder::class,
            CamerasSeeder::class,
            DetectionsSeeder::class,
        ]);
    }
}