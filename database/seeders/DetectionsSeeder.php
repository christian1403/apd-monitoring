<?php

namespace Database\Seeders;

use App\Models\Camera;
use App\Models\Detection;
use App\Models\DetectionItem;
use App\Models\Item;
use Illuminate\Database\Seeder;

class DetectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding detections...');
        $items = Item::inRandomOrder()->limit(3)->get();
        $cameras = Camera::all();

        // Group items into detections per camera
        foreach ($cameras as $camera) {
            $detection = Detection::create([
                'camera_id' => $camera->id,
                'location_id' => $camera->location_id,
                'status' => 'safe',
                'detected_at' => now(),
            ]);

            foreach ($items as $item) {
                $itemStatus = fake()->randomElement(['detected', 'undetected']);

                DetectionItem::create([
                    'item_id' => $item->id,
                    'detection_id' => $detection->id,
                    'status' => $itemStatus,
                ]);
            }

            // Recalculate detection status based on items
            $allDetected = $detection->detectionItems()
                ->where('status', 'undetected')
                ->count() === 0;

            $detection->update(['status' => $allDetected ? 'safe' : 'unsafe']);
        }

        $this->command->info($cameras->count().' detections with '.$items->count().' items each seeded successfully!');
    }
}
