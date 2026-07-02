<?php

namespace App\Repositories;

use App\Infrastructure\BaseRepository;
use App\Models\Location;
use App\Repositories\Contracts\LocationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LocationRepository extends BaseRepository implements LocationRepositoryInterface
{
    public function __construct(Location $model)
    {
        parent::__construct($model);
    }

    public function getData(
        string $search = '',
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        int $perPage = 10,
        ?array $where = null
    ): LengthAwarePaginator {
        $query = $this->model->newQuery();

        if ($search !== '') {
            $this->applyQuerySearch($query, $search, ['name', 'description']);
        }

        if ($where) {
            $query->where($where);
        }

        $this->applySorting($query, $sortBy, strtoupper($sortDir));

        return $this->paginateQuery($query, $perPage);
    }

    public function exportData(
        string $search = '',
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        ?array $where = null
    ): Collection {
        $query = $this->model->newQuery();

        if ($search !== '') {
            $this->applyQuerySearch($query, $search, ['name', 'description']);
        }

        if ($where) {
            $query->where($where);
        }

        $this->applySorting($query, $sortBy, strtoupper($sortDir));

        return $query->get();
    }
}
