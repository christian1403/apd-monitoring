<?php

namespace App\Repositories;

use App\Infrastructure\BaseRepository;
use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ItemRepository extends BaseRepository implements ItemRepositoryInterface
{
    public function __construct(Item $model)
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
        $query = $this->model->newQuery();

        if ($search !== '') {
            $this->applyQuerySearch($query, $search, ['name', 'description']);
        }

        if ($where) $query->where($where);

        $this->applySorting($query, $sortBy, strtoupper($sortDir));

        return $this->paginateQuery($query, $perPage);
    }
}