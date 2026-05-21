<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ViolationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // You can use the DB facade to insert data into the violations table
        \DB::table('violations')->insert([
            ['item_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['item_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['item_id' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
