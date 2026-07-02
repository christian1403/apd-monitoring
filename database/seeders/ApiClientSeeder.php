<?php

namespace Database\Seeders;

use App\Models\ApiClient;
use App\Models\Camera;
use Illuminate\Database\Seeder;

class ApiClientSeeder extends Seeder
{
    public function run(): void
    {
        $camera = Camera::first();

        $client = ApiClient::create([
            'name' => 'Test Camera Client',
            'camera_id' => $camera?->id,
        ]);

        $this->command->info('API Client created:');
        $this->command->info("  API Key:    {$client->api_key}");
        $this->command->info("  API Secret: {$client->api_secret}");
    }
}
