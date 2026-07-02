<?php

namespace App\Services;

use App\Infrastructure\BaseService;
use App\Models\Location;
use App\Repositories\Contracts\LocationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LocationService extends BaseService
{
    public function __construct(
        protected LocationRepositoryInterface $locationRepository,
        protected FileService $fileService,
    ) {}

    public function getAllLocation()
    {
        return $this->locationRepository->getAll();
    }

    public function getPaginatedLocation(
        string $search = '',
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        int $perPage = 10,
        array $where = []
    ): LengthAwarePaginator {
        return $this->locationRepository->getData($search, $sortBy, $sortDir, $perPage);
    }

    public function findLocation(int $id): ?Location
    {
        /** @var Location|null */
        return $this->locationRepository->findOne($id);
    }

    public function createLocation(array $data): Location
    {

        /** @var Location */
        return $this->locationRepository->save($data);
    }

    public function updateLocation(int $id, array $data): bool
    {
        return $this->locationRepository->update($id, $data);
    }

    public function deleteLocation(int $id): bool
    {
        $existing = $this->locationRepository->findOne($id);

        return $this->locationRepository->delete($id);
    }

    public function getExportData(
        string $search = '',
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        array $where = []
    ): Collection {
        return $this->locationRepository->exportData($search, $sortBy, $sortDir, $where);
    }
}
