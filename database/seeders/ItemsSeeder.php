<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding items...');
        $items = [
            ['name' => 'Baju Hazmat', 'code' => 'baju_hazmat', 'description' => 'Baju pelindung untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Sendal Produksi', 'code' => 'sendal_produksi', 'description' => 'Sendal khusus untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Masker', 'code' => 'masker', 'description' => 'Masker pelindung untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Sarung Tangan', 'code' => 'sarung_tangan', 'description' => 'Sarung tangan pelindung untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Kacamata Pelindung', 'code' => 'kacamata_pelindung', 'description' => 'Kacamata untuk melindungi mata pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Sepatu Safety', 'code' => 'sepatu_safety', 'description' => 'Sepatu khusus untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Helm Keselamatan', 'code' => 'helm_keselamatan', 'description' => 'Helm untuk melindungi kepala pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Rompi Reflektif', 'code' => 'rompi_reflektif', 'description' => 'Rompi dengan bahan reflektif untuk meningkatkan visibilitas pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Earplug', 'code' => 'earplug', 'description' => 'Alat pelindung pendengaran untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Masker Debu', 'code' => 'masker_debu', 'description' => 'Masker khusus untuk melindungi pekerja produksi dari debu dan partikel berbahaya', 'image' => null, 'is_active' => true],
            ['name' => 'Baju Kerja', 'code' => 'baju_kerja', 'description' => 'Baju kerja untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Celana Kerja', 'code' => 'celana_kerja', 'description' => 'Celana kerja untuk pekerja produksi', 'image' => null, 'is_active' => true],
            ['name' => 'Sarung Tangan Karet', 'code' => 'sarung_tangan_karet', 'description' => 'Sarung tangan karet untuk pekerja produksi yang bekerja dengan bahan kimia', 'image' => null, 'is_active' => true],
            ['name' => 'Sepatu Bot', 'code' => 'sepatu_bot', 'description' => 'Sepatu bot untuk pekerja produksi yang bekerja di lingkungan basah atau berlumpur', 'image' => null, 'is_active' => true],
            ['name' => 'Pelindung Wajah', 'code' => 'pelindung_wajah', 'description' => 'Pelindung wajah untuk pekerja produksi yang bekerja dengan bahan berbahaya atau berpotensi meledak', 'image' => null, 'is_active' => true],
        ];
        Item::insert($items);
        $this->command->info(count($items).' items seeded successfully!');
    }
}
