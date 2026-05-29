<?php

namespace App\Http\Controllers;

use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Requests\Location\UpdateLocationRequest;
use App\Services\LocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exports\LocationsExport;

class LocationController extends Controller
{
    public function __construct(
        protected LocationService $locationService
    ) {}

    public function index(Request $request): Response
    {
        $search  = $request->string('search', '')->toString();
        $sortBy  = in_array($request->string('sort_by')->toString(), ['name', 'created_at'])
                        ? $request->string('sort_by')->toString()
                        : 'created_at';
        $sortDir = in_array($request->string('sort_dir')->toString(), ['asc', 'desc'])
                        ? $request->string('sort_dir')->toString()
                        : 'desc';
        $perPage = min(max($request->integer('per_page', 10), 10), 100);
        
        $paginator = $this->locationService->getPaginatedLocation($search, $sortBy, $sortDir, $perPage);
        return Inertia::render('location/Master', [
            'items'     => $paginator,
            'pageTitle' => 'Locations',
            'filters'   => [
                'search'   => $search,
                'sort_by'  => $sortBy,
                'sort_dir' => $sortDir,
                'per_page' => $perPage,
            ],
        ]);                                                                  
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $this->locationService->createLocation(
            $request->safe()->except('image'),
            $request->file('image'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Location created.')]);

        return to_route('location.index');
    }

    public function update(UpdateLocationRequest $request, int $id): RedirectResponse
    {
        $this->locationService->updateLocation($id, $request->safe()->except('image'), $request->file('image'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Location updated.')]);

        return to_route('location.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->locationService->deleteLocation($id);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Location deleted.')]);

        return to_route('location.index');
    }

    public function export(Request $request, string $format): BinaryFileResponse
    {
        $format = in_array($format, ['xlsx', 'csv']) ? $format : 'xlsx';

        $search  = $request->string('search', '')->toString();
        $sortBy  = in_array($request->string('sort_by')->toString(), ['name', 'created_at'])
                        ? $request->string('sort_by')->toString()
                        : 'created_at';
        $sortDir = in_array($request->string('sort_dir')->toString(), ['asc', 'desc'])
                        ? $request->string('sort_dir')->toString()
                        : 'desc';

        return Excel::download(new LocationsExport(
            search: $search,
            sortBy: $sortBy,
            sortDir: $sortDir,
        ), 'locations.' . $format);
    }
}

