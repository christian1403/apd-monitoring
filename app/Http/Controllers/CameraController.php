<?php

namespace App\Http\Controllers;

use App\Exports\CamerasExport;
use App\Http\Requests\Cameras\StoreCameraRequest;
use App\Http\Requests\Cameras\UpdateCameraRequest;
use App\Models\Camera;
use App\Models\Location;
use App\Services\CameraService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Enums\CameraStatus;

class CameraController extends Controller
{
    public function __construct(
        protected CameraService $cameraService,
    ) {}

    public function index(Request $request): Response
    {
        $search  = $request->string('search', '')->toString();
        $sortBy  = in_array($request->string('sort_by')->toString(), ['name', 'ip_address', 'status', 'created_at'])
                        ? $request->string('sort_by')->toString()
                        : 'created_at';
        $sortDir = in_array($request->string('sort_dir')->toString(), ['asc', 'desc'])
                        ? $request->string('sort_dir')->toString()
                        : 'desc';
        $perPage = min(max($request->integer('per_page', 10), 10), 100);
        $status  = in_array($request->string('status')->toString(), array_merge(['all'], array_map(fn($case) => $case->value, CameraStatus::cases())))
                        ? $request->string('status')->toString()
                        : 'all';

        $where = [];
        if ($status !== 'all') {
            $where['status'] = $status;
        }

        $cameras = $this->cameraService->getPaginatedCameras($search, $sortBy, $sortDir, $perPage, $where);
        
        return Inertia::render('camera/Master', [
            'cameras'   => $cameras,
            'locations' => Location::orderBy('name')->get(),
            'statuses'  => CameraStatus::cases(),
            'filters'   => [
                'search'   => $search,
                'sort_by'  => $sortBy,
                'sort_dir' => $sortDir,
                'per_page' => $perPage,
                'status'   => $status,
            ],
        ]);
    }

    public function store(StoreCameraRequest $request): RedirectResponse
    {
        $this->cameraService->createCamera(
            $request->safe()->except('image'),
            $request->file('image'),
        );
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Camera created.')]);
        return redirect()->route('camera.index');
    }

    public function update(UpdateCameraRequest $request, Camera $camera): RedirectResponse
    {
        $this->cameraService->updateCamera(
            $camera->id,
            $request->safe()->except('image'),
            $request->file('image'),
        );
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Camera updated.')]);
        return redirect()->route('camera.index');
    }

    public function destroy(Camera $camera): RedirectResponse
    {
        $this->cameraService->deleteCamera($camera->id);
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Camera deleted.')]);
        return redirect()->route('camera.index');
    }

    public function export(Request $request, string $format): BinaryFileResponse
    {
        $format = in_array($format, ['xlsx', 'csv']) ? $format : 'xlsx';

        $search  = $request->string('search', '')->toString();
        $sortBy  = in_array($request->string('sort_by')->toString(), ['name', 'ip_address', 'status', 'created_at'])
                        ? $request->string('sort_by')->toString()
                        : 'created_at';
        $sortDir = in_array($request->string('sort_dir')->toString(), ['asc', 'desc'])
                        ? $request->string('sort_dir')->toString()
                        : 'desc';
        $status  = in_array($request->string('status')->toString(), array_merge(['all'], array_map(fn($case) => $case->value, CameraStatus::cases())))
                        ? $request->string('status')->toString()
                        : 'all';

        $where = [];
        if ($status !== 'all') {
            $where['status'] = $status;
        }

        return Excel::download(
            new CamerasExport($search, $sortBy, $sortDir, $where),
            'cameras.' . $format,
        );
    }
}
