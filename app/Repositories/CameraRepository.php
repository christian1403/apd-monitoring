<?php

namespace App\Repositories;

use App\Infrastructure\BaseRepository;
use App\Models\Camera;
use App\Repositories\Contracts\CameraRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use \Illuminate\Support\Collection;
class CameraRepository extends BaseRepository implements CameraRepositoryInterface
{
    public function __construct(Camera $model)
    {
        parent::__construct($model);
    }

    public function getData(
        string $search = '',
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        int $perPage = 10,
        array $where = null
    ): LengthAwarePaginator {
        $query = $this->model->newQuery()->with('location');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('location', function ($lq) use ($search) {
                        $lq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($where) $query->where($where);

        $this->applySorting($query, $sortBy, strtoupper($sortDir));

        return $this->paginateQuery($query, $perPage);
    }

    public function exportData(
        string $search = '',
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        array $where = null
    ): Collection {
        $query = $this->model->newQuery()->with('location');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('location', function ($lq) use ($search) {
                        $lq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($where) $query->where($where);

        $this->applySorting($query, $sortBy, strtoupper($sortDir));

        return $query->get();
    }
}