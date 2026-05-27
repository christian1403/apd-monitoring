<?php

namespace App\Services;
use App\Infrastructure\BaseService;
use App\Repositories\Contracts\ItemRepositoryInterface;
class ItemService extends BaseService
{
    public function __construct(
        protected ItemRepositoryInterface $itemRepository
    ) {}

    public function getAllItems()
    {
        return $this->itemRepository->all();
    }
}

?>