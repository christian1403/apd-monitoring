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
        ];
        Item::insert($items);
        $this->command->info(count($items) . ' items seeded successfully!');
    }
}
