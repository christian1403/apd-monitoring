<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use \Illuminate\Support\Collection;

interface LocationRepositoryInterface
{
    public function getData(
        string $search,
        string $sortBy,
        string $sortDir,
        int $perPage,
        array $where = null
    ): LengthAwarePaginator;

    public function exportData(
        string $search,
        string $sortBy,
        string $sortDir,
        array $where = null
    ): Collection;
}
