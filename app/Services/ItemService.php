<?php

namespace App\Services;

use App\Infrastructure\BaseService;
use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Http\UploadedFile;

class ItemService extends BaseService
{
    public function __construct(
        protected ItemRepositoryInterface $itemRepository,
        protected FileService $fileService,
    ) {}

    public function getAllItems()
    {
        return $this->itemRepository->getAll();
    }

    public function findItem(int $id): ?Item
    {
        /** @var Item|null */
        return $this->itemRepository->findOne($id);
    }

    public function createItem(array $data, ?UploadedFile $image = null): Item
    {
        if ($image) {
            $data['image'] = $this->fileService->store($image, 'items');
        }

        $data['is_active'] = $data['is_active'] ?? true;

        /** @var Item */
        return $this->itemRepository->save($data);
    }

    public function updateItem(int $id, array $data, ?UploadedFile $image = null): bool
    {
        if ($image) {
            $existing = $this->itemRepository->findOne($id);

            if ($existing?->image) {
                $this->fileService->delete($existing->image);
            }

            $data['image'] = $this->fileService->store($image, 'items');
        }

        return $this->itemRepository->update($id, $data);
    }

    public function deleteItem(int $id): bool
    {
        $existing = $this->itemRepository->findOne($id);

        if ($existing?->image) {
            $this->fileService->delete($existing->image);
        }

        return $this->itemRepository->delete($id);
    }
}
