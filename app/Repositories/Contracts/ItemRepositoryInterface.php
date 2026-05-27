<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ItemRepositoryInterface
{
    public function getData(
        string $search,
        string $sortBy,
        string $sortDir,
        int $perPage,
        array $where = null
    ): LengthAwarePaginator;
}
