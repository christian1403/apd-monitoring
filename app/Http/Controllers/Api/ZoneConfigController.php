<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ZoneConfigController extends Controller
{
    private function pythonUrl(): string
    {
        return rtrim(config('services.python_api.url', 'http://127.0.0.1:8001'), '/') . '/api/config';
    }

    public function show(Camera $camera): JsonResponse
    {
        try {
            $response = Http::timeout(5)->get($this->pythonUrl());

            if (!$response->successful()) {
                return response()->json(null, 404);
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Python API unavailable: ' . $e->getMessage()], 502);
        }
    }

    public function store(Request $request, Camera $camera): JsonResponse
    {
        $validated = $request->validate([
            'green_line' => 'nullable|array',
            'green_line.*' => 'array',
            'green_line.*.*' => 'numeric',
            'red_zone_polygons' => 'nullable|array',
            'red_zone_polygons.*' => 'array',
            'red_zone_polygons.*.*' => 'array',
            'red_zone_polygons.*.*.*' => 'numeric',
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(5)->post($this->pythonUrl(), $validated);

            if (!$response->successful()) {
                return response()->json(['error' => 'Python API error: ' . $response->body()], $response->status());
            }

            return response()->json($response->json(), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Python API unavailable: ' . $e->getMessage()], 502);
        }
    }
}
