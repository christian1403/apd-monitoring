<?php

namespace App\Http\Controllers;

use App\Http\Requests\Items\StoreItemRequest;
use App\Http\Requests\Items\UpdateItemRequest;
use App\Services\FileService;
use App\Services\ItemService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function __construct(
        protected ItemService $itemService,
        protected FileService $fileService,
    ) {}

    public function index(): Response
    {
        $items = $this->itemService->getAllItems();

        return Inertia::render('items/Master', [
            'items'     => $items,
            'pageTitle' => 'Items',
        ]);
    }

    public function store(StoreItemRequest $request): RedirectResponse
    {
        $this->itemService->createItem(
            $request->safe()->except('image'),
            $request->file('image'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item created.')]);

        return to_route('items.index');
    }

    public function update(UpdateItemRequest $request, int $id): RedirectResponse
    {
        $this->itemService->updateItem(
            $id,
            $request->safe()->except('image'),
            $request->file('image'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item updated.')]);

        return to_route('items.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->itemService->deleteItem($id);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item deleted.')]);

        return to_route('items.index');
    }
}

