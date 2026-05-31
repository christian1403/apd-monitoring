<?php

namespace App\Services;

use App\Infrastructure\BaseService;
use App\Models\Camera;
use App\Models\Detection;
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

        /** @var Detection */
        return $this->detectionRepository->save($data);
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

        return $this->detectionRepository->update($id, $data);
    }

    public function deleteDetection(int $id): bool
    {
        $existing = $this->detectionRepository->findOne($id);

        if ($existing?->image) {
            $this->fileService->delete($existing->image);
        }

        return $this->detectionRepository->delete($id);
    }
}
