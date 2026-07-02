<?php

namespace App\Http\Controllers;

use App\Exports\ItemsExport;
use App\Http\Requests\Items\StoreItemRequest;
use App\Http\Requests\Items\UpdateItemRequest;
use App\Services\FileService;
use App\Services\ItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ItemController extends Controller
{
    public function __construct(
        protected ItemService $itemService,
        protected FileService $fileService,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search', '')->toString();
        $sortBy = in_array($request->string('sort_by')->toString(), ['name', 'is_active', 'created_at'])
                        ? $request->string('sort_by')->toString()
                        : 'created_at';
        $sortDir = in_array($request->string('sort_dir')->toString(), ['asc', 'desc'])
                        ? $request->string('sort_dir')->toString()
                        : 'desc';
        $perPage = min(max($request->integer('per_page', 10), 10), 100);
        $status = in_array($request->string('status')->toString(), ['all', 'active', 'inactive'])
                        ? $request->string('status')->toString()
                        : 'all';

        $where = [];
        if ($status === 'active') {
            $where['is_active'] = true;
        } elseif ($status === 'inactive') {
            $where['is_active'] = false;
        }

        $paginator = $this->itemService->getPaginatedItems($search, $sortBy, $sortDir, $perPage, $where);

        return Inertia::render('items/Master', [
            'items' => $paginator,
            'pageTitle' => 'Items',
            'filters' => [
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
                'per_page' => $perPage,
                'status' => $status,
            ],
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

    public function export(Request $request, string $format): BinaryFileResponse
    {
        $format = in_array($format, ['xlsx', 'csv']) ? $format : 'xlsx';

        $search = $request->string('search', '')->toString();
        $sortBy = in_array($request->string('sort_by')->toString(), ['name', 'is_active', 'created_at'])
                        ? $request->string('sort_by')->toString()
                        : 'created_at';
        $sortDir = in_array($request->string('sort_dir')->toString(), ['asc', 'desc'])
                        ? $request->string('sort_dir')->toString()
                        : 'desc';
        $status = in_array($request->string('status')->toString(), ['all', 'active', 'inactive'])
                        ? $request->string('status')->toString()
                        : 'all';

        $where = [];
        if ($status === 'active') {
            $where['is_active'] = true;
        } elseif ($status === 'inactive') {
            $where['is_active'] = false;
        }

        return Excel::download(
            new ItemsExport($search, $sortBy, $sortDir, $where),
            'items.'.$format,
        );
    }
}
