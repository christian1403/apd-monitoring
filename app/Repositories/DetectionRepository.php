<?php

namespace App\Repositories;

use App\Infrastructure\BaseRepository;
use App\Models\Detection;
use App\Repositories\Contracts\DetectionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use \Illuminate\Support\Collection;
class DetectionRepository extends BaseRepository implements DetectionRepositoryInterface
{
    public function __construct(Detection $model)
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
        $query = $this->model->newQuery()->with(['item', 'camera', 'location']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('status', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($iq) use ($search) {
                        $iq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('camera', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('ip_address', 'like', "%{$search}%");
                    })
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
        $query = $this->model->newQuery()->with(['item', 'camera', 'location']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('status', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($iq) use ($search) {
                        $iq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('camera', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('ip_address', 'like', "%{$search}%");
                    })
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