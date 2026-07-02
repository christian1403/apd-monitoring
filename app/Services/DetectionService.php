<?php

namespace App\Services;

use App\Infrastructure\BaseService;
use App\Models\Camera;
use App\Models\Detection;
use App\Models\DetectionItem;
use App\Repositories\Contracts\DetectionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class DetectionService extends BaseService
{
    public function __construct(
        protected DetectionRepositoryInterface $detectionRepository,
        protected FileService $fileService,
    ) {}

    public function getPaginatedDetections(
        string $search = '',
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        int $perPage = 10,
        array $where = []
    ): LengthAwarePaginator {
        return $this->detectionRepository->getData($search, $sortBy, $sortDir, $perPage, $where ?: null);
    }

    public function findDetection(int $id): ?Detection
    {
        /** @var Detection|null */
        return $this->detectionRepository->findOne($id);
    }

    public function createDetection(array $data, ?UploadedFile $image = null): Detection
    {
        $camera = Camera::findOrFail($data['camera_id']);

        if (empty($data['location_id'])) {
            $data['location_id'] = $camera->location_id;
        }

        if (empty($data['detected_at'])) {
            $data['detected_at'] = now();
        }

        if ($image) {
            $data['image'] = $this->fileService->store($image, 'detections');
        }

        $items = $data['items'] ?? [];
        unset($data['items']);

        // Auto-calculate detection status: all detected → safe, otherwise → unsafe
        $data['status'] = collect($items)->every(fn ($item) => $item['status'] === 'detected')
            ? 'safe'
            : 'unsafe';

        /** @var Detection */
        $detection = $this->detectionRepository->save($data);

        foreach ($items as $item) {
            DetectionItem::create([
                'detection_id' => $detection->id,
                'item_id' => $item['item_id'],
                'status' => $item['status'],
            ]);
        }

        return $detection;
    }

    public function updateDetection(int $id, array $data, ?UploadedFile $image = null): bool
    {
        $camera = Camera::findOrFail($data['camera_id']);

        if (empty($data['location_id'])) {
            $data['location_id'] = $camera->location_id;
        }

        if (empty($data['detected_at'])) {
            $existing = $this->detectionRepository->findOne($id);
            $data['detected_at'] = $existing?->detected_at ?? now();
        }

        if ($image) {
            $existing = $this->detectionRepository->findOne($id);

            if ($existing?->image) {
                $this->fileService->delete($existing->image);
            }

            $data['image'] = $this->fileService->store($image, 'detections');
        }

        $items = $data['items'] ?? [];
        unset($data['items']);

        // Auto-calculate detection status: all detected → safe, otherwise → unsafe
        $data['status'] = collect($items)->every(fn ($item) => $item['status'] === 'detected')
            ? 'safe'
            : 'unsafe';

        $result = $this->detectionRepository->update($id, $data);

        // Sync detection items: delete old, create new
        DetectionItem::where('detection_id', $id)->delete();

        foreach ($items as $item) {
            DetectionItem::create([
                'detection_id' => $id,
                'item_id' => $item['item_id'],
                'status' => $item['status'],
            ]);
        }

        return $result;
    }

    public function deleteDetection(int $id): bool
    {
        $existing = $this->detectionRepository->findOne($id);

        if ($existing?->image) {
            $this->fileService->delete($existing->image);
        }

        return $this->detectionRepository->delete($id);
    }

    public function getExportData(
        string $search = '',
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        array $where = []
    ) {
        return $this->detectionRepository->exportData($search, $sortBy, $sortDir, $where ?: null);
    }
}
