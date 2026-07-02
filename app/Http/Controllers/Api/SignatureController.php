<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiClient;
use App\Traits\ApiResponseTrait;
use App\Utilities\HmacSignature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignatureController extends Controller
{
    use ApiResponseTrait;

    /**
     * Dev helper: generate auth headers and a ready-to-use curl command
     * for the detection/violation endpoint.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'camera_id' => ['required', 'integer', 'exists:cameras,id'],
        ]);

        $apiKey = $request->header('X-Api-Key');
        $apiSecret = $request->header('X-Api-Secret');
        $cameraId = $request->integer('camera_id');

        if (! $apiKey || ! $apiSecret) {
            return $this->errorResponse('Missing required headers: X-Api-Key, X-Api-Secret.', 422);
        }

        // Verify the api_key exists and matches the secret
        $client = ApiClient::where('api_key', $apiKey)
            ->where('api_secret', $apiSecret)
            ->where('is_active', true)
            ->first();

        if (! $client) {
            return $this->errorResponse('Invalid API credentials.', 401);
        }

        $headers = HmacSignature::headers($apiKey, $apiSecret, $cameraId);
        $timestamp = $headers['X-Timestamp'];
        $signature = $headers['X-Signature'];

        $curl = implode(" \\\n  ", [
            'curl -X POST '.url('/api/v1/detection/violation'),
            '-H "X-Api-Key: '.$apiKey.'"',
            '-H "X-Timestamp: '.$timestamp.'"',
            '-H "X-Signature: '.$signature.'"',
            '-F "camera_id='.$cameraId.'"',
            '-F \'items=[{"code":"masker","status":"detected"}]\'',
            '-F "image=@/path/to/image.jpg"',
        ]);

        return $this->successResponse([
            'headers' => $headers,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'curl' => $curl,
        ], 'Signature generated.');
    }
}
