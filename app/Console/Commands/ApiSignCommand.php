<?php

namespace App\Console\Commands;

use App\Models\ApiClient;
use App\Utilities\HmacSignature;
use Illuminate\Console\Command;

class ApiSignCommand extends Command
{
    protected $signature = 'api:sign
        {--client= : Api client ID (default: first active client)}
        {--camera-id=1 : Camera ID for the payload}
        {--curl : Print a ready-to-paste curl command}';

    protected $description = 'Generate HMAC signature headers for API testing';

    public function handle(): int
    {
        $clientId = $this->option('client');

        $client = $clientId
            ? ApiClient::findOrFail($clientId)
            : ApiClient::where('is_active', true)->first();

        if (! $client) {
            $this->error('No active API client found. Run: php artisan db:seed --class=ApiClientSeeder');

            return self::FAILURE;
        }

        $cameraId = (int) $this->option('camera-id');

        $headers = HmacSignature::headers(
            $client->api_key,
            $client->api_secret,
            $cameraId,
        );

        if ($this->option('curl')) {
            $url = url('/api/v1/detection/violation');
            $this->line("curl -X POST {$url} \\");
            $this->line("  -H \"X-Api-Key: {$headers['X-Api-Key']}\" \\");
            $this->line("  -H \"X-Timestamp: {$headers['X-Timestamp']}\" \\");
            $this->line("  -H \"X-Signature: {$headers['X-Signature']}\" \\");
            $this->line("  -F \"camera_id={$cameraId}\" \\");
            $this->line('  -F \'items=[{"code":"masker","status":"detected"}]\' \\');
            $this->line('  -F "image=@/path/to/image.jpg"');
        } else {
            $this->table(['Header', 'Value'], [
                ['X-Api-Key', $headers['X-Api-Key']],
                ['X-Timestamp', $headers['X-Timestamp']],
                ['X-Signature', $headers['X-Signature']],
            ]);
        }

        return self::SUCCESS;
    }
}
