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

        // ITEMS / APD
        $helm = Item::updateOrCreate(
            ['name' => 'Helm Safety'],
            ['is_active' => true]
        );

        $masker = Item::updateOrCreate(
            ['name' => 'Masker'],
            ['is_active' => true]
        );

        $rompi = Item::updateOrCreate(
            ['name' => 'Rompi Safety'],
            ['is_active' => true]
        );

        // LOCATIONS
        $gudang = Location::updateOrCreate(
            ['name' => 'Gudang Utama'],
            ['description' => 'Area penyimpanan barang']
        );

        $produksi = Location::updateOrCreate(
            ['name' => 'Area Produksi'],
            ['description' => 'Area utama proses produksi']
        );

        // CAMERAS
        $cameraGudang = Camera::updateOrCreate(
            ['ip_address' => '192.168.1.10'],
            [
                'name' => 'Kamera Gudang 1',
                'status' => 'active',
                'location_id' => $gudang->id,
            ]
        );

        $cameraProduksi = Camera::updateOrCreate(
            ['ip_address' => '192.168.1.11'],
            [
                'name' => 'Kamera Produksi 1',
                'status' => 'active',
                'location_id' => $produksi->id,
            ]
        );

        // DETECTIONS
Detection::updateOrCreate(
    [
        'item_id' => $helm->id,
        'camera_id' => $cameraGudang->id,
        'location_id' => $gudang->id,
    ],
    [
        'status' => 'safe',
        'image' => 'detections/helm-gudang.jpg',
    ]
);

Detection::updateOrCreate(
    [
        'item_id' => $masker->id,
        'camera_id' => $cameraProduksi->id,
        'location_id' => $produksi->id,
    ],
    [
        'status' => 'safe',
        'image' => 'detections/masker-produksi.jpg',
    ]
);

Detection::updateOrCreate(
    [
        'item_id' => $rompi->id,
        'camera_id' => $cameraProduksi->id,
        'location_id' => $produksi->id,
    ],
    [
        'status' => 'violate',
        'image' => 'detections/rompi-produksi.jpg',
    ]
);
    }
}