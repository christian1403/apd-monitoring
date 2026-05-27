<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Camera;
use App\Models\Location;

class CamerasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = Location::all();
        $this->command->info('Seeding cameras...');
        foreach ($locations as $location) {
            Camera::updateOrCreate(
                ['name' => 'Camera 1 - ' . $location->name],
                [
                    'location_id' => $location->id,
                    'ip_address' => '192.168.1.' . $location->id,
                    'status' => 'active',
                    'image' => null,
                ]
            );
        }
        $this->command->info(count($locations) . ' cameras seeded successfully!');
    }
}
