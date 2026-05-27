<?php

namespace App\Repositories;
use App\Infrastructure\BaseRepository;
use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Models\Item;

class ItemRepository extends BaseRepository implements ItemRepositoryInterface
{
    public function __construct(Item $model)
    {
        parent::__construct($model);
    }
}

?>