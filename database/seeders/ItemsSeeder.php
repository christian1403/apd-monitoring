<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding items...');
        $items = [
            ['name' => 'Baju Hazmat', 'description' => 'Baju pelindung untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Sendal Produksi', 'description' => 'Sendal khusus untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Masker', 'description' => 'Masker pelindung untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Sarung Tangan', 'description' => 'Sarung tangan pelindung untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Kacamata Pelindung', 'description' => 'Kacamata untuk melindungi mata pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Sepatu Safety', 'description' => 'Sepatu khusus untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Helm Keselamatan', 'description' => 'Helm untuk melindungi kepala pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Rompi Reflektif', 'description' => 'Rompi dengan bahan reflektif untuk meningkatkan visibilitas pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Earplug', 'description' => 'Alat pelindung pendengaran untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Masker Debu', 'description' => 'Masker khusus untuk melindungi pekerja produksi dari debu dan partikel berbahaya', 'image' => null, 'is_active' => true],
            ['name' => 'Baju Kerja', 'description' => 'Baju kerja untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Celana Kerja', 'description' => 'Celana kerja untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Sarung Tangan Karet', 'description' => 'Sarung tangan karet untuk pekerja produksi yang bekerja dengan bahan kimia', 'image' => null, 'is_active' => true],
            ['name' => 'Sepatu Bot', 'description' => 'Sepatu bot untuk pekerja produksi yang bekerja di lingkungan basah atau berlumpur', 'image' => null, 'is_active' => true],
            ['name' => 'Pelindung Wajah', 'description' => 'Pelindung wajah untuk pekerja produksi yang bekerja dengan bahan berbahaya atau berpotensi meledak', 'image' => null, 'is_active' => true],
        ];
        Item::insert($items);
        $this->command->info(count($items) . ' items seeded successfully!');
    }
}
