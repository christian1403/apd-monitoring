<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Detection;
use App\Models\Item;
use App\Models\Camera;
use App\Models\Location;
use App\Models\DetectionItem;

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
            $detection = Detection::updateOrCreate(
                ['camera_id' => $camera->id],
                [
                    'location_id' => $camera->location_id,
                    'detected_at' => now(),
                ]
            );
            DetectionItem::create([
                'item_id' => $item->id,
                'detection_id' => $detection->id,
                'status' => 'safe'
            ]);
        }
        $this->command->info(count($items) . ' detections seeded successfully!');
    }
}
