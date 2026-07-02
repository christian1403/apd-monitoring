<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiDetectionRequest;
use App\Models\Item;
use App\Services\DetectionService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class DetectionController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected DetectionService $detectionService,
    ) {}

    public function store(StoreApiDetectionRequest $request): JsonResponse
    {
        $data = $request->safe()->except('image');

        // Resolve item codes to item_ids
        $data['items'] = collect($data['items'])->map(function ($item) {
            $resolved = Item::where('code', $item['code'])->firstOrFail();

            return [
                'item_id' => $resolved->id,
                'status' => $item['status'],
            ];
        })->toArray();

        $detection = $this->detectionService->createDetection(
            $data,
            $request->file('image'),
        );

        return $this->successResponse(
            $detection->load(['detectionItems.item', 'camera', 'location']),
            'Detection violation recorded.',
            201,
        );
    }
}
