<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Detection;
use App\Models\Item;
use App\Models\Camera;
use App\Models\Location;

class DetectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding detections...');
        $items = Item::all();
        foreach($items as $item) {
            $camera = Camera::inRandomOrder()->first();
            Detection::updateOrCreate(
                ['camera_id' => $camera->id, 'item_id' => $item->id],
                [
                    'location_id' => $camera->location_id,
                    'detected_at' => now(),
                ]
            );
        }
        $this->command->info(count($items) . ' detections seeded successfully!');
    }
}
