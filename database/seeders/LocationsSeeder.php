<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding locations...');
        $locations = [
            ['name' => 'Gudang A', 'description' => 'Gudang untuk menyimpan APD produksi', 'address' => 'Jl. Industri No. 123, Kawasan Industri, Kota ABC', 'latitude' => -6.200000, 'longitude' => 106.816666],
            ['name' => 'Gudang B', 'description' => 'Gudang untuk menyimpan APD produksi', 'address' => 'Jl. Industri No. 456, Kawasan Industri, Kota ABC', 'latitude' => -6.210000, 'longitude' => 106.826666],
            ['name' => 'Produksi A', 'description' => 'Area produksi A', 'address' => 'Jl. Produksi No. 789, Kawasan Industri, Kota ABC', 'latitude' => -6.220000, 'longitude' => 106.836666],
        ];
        Location::insert($locations);
        $this->command->info(count($locations).' locations seeded successfully!');
    }
}
