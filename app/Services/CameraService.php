<?php

namespace App\Services;

use App\Infrastructure\BaseService;
use App\Models\Camera;
use App\Repositories\Contracts\CameraRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class CameraService extends BaseService
{
    public function __construct(
        protected CameraRepositoryInterface $cameraRepository,
        protected FileService $fileService,
    ) {}

    public function getPaginatedCameras(
        string $search = '',
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        int $perPage = 10,
        array $where = []
    ): LengthAwarePaginator {
        return $this->cameraRepository->getData($search, $sortBy, $sortDir, $perPage, $where ?: null);
    }

    public function findCamera(int $id): ?Camera
    {
        /** @var Camera|null */
        return $this->cameraRepository->findOne($id);
    }

    public function createCamera(array $data, ?UploadedFile $image = null): Camera
    {
        if ($image) {
            $data['image'] = $this->fileService->store($image, 'cameras');
        }

        /** @var Camera */
        return $this->cameraRepository->save($data);
    }

    public function updateCamera(int $id, array $data, ?UploadedFile $image = null): bool
    {
        if ($image) {
            $existing = $this->cameraRepository->findOne($id);

            if ($existing?->image) {
                $this->fileService->delete($existing->image);
            }

            $data['image'] = $this->fileService->store($image, 'cameras');
        }

        return $this->cameraRepository->update($id, $data);
    }

    public function deleteCamera(int $id): bool
    {
        $existing = $this->cameraRepository->findOne($id);

        if ($existing?->image) {
            $this->fileService->delete($existing->image);
        }

        return $this->cameraRepository->delete($id);
    }
}
