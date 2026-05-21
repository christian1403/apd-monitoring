<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // You can use the DB facade to insert data into the items table
        \DB::table('items')->insert([
            ['name' => 'Baju Hazmat', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sendal Produksi', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Masker', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
