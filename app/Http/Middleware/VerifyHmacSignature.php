<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use App\Utilities\HmacSignature;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyHmacSignature
{
    /**
     * Timestamp tolerance in minutes.
     */
    protected int $toleranceMinutes = 5;

    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Api-Key');
        $timestamp = $request->header('X-Timestamp');
        $signature = $request->header('X-Signature');

        if (! $apiKey || ! $timestamp || ! $signature) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required auth headers: X-Api-Key, X-Timestamp, X-Signature.',
            ], 401);
        }

        $client = ApiClient::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (! $client) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key.',
            ], 401);
        }

        // Check timestamp freshness
        try {
            $requestTime = Carbon::parse($timestamp);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid timestamp format.',
            ], 401);
        }

        if ($requestTime->diffInMinutes(now()) > $this->toleranceMinutes) {
            return response()->json([
                'success' => false,
                'message' => 'Request timestamp expired.',
            ], 401);
        }

        $cameraId = $request->integer('camera_id');

        if (! HmacSignature::verify($client->api_secret, $timestamp, $cameraId, $signature)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature.',
            ], 401);
        }

        // Attach client to request for downstream use
        $request->attributes->set('api_client', $client);

        return $next($request);
    }
}
