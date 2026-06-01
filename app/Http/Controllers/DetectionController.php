<?php

namespace App\Http\Controllers;

use App\Exports\DetectionsExport;
use App\Http\Requests\Detections\StoreDetectionRequest;
use App\Http\Requests\Detections\UpdateDetectionRequest;
use App\Models\Camera;
use App\Models\Detection;
use App\Models\Item;
use App\Models\Location;
use App\Services\DetectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Enums\DetectionStatus;

class DetectionController extends Controller
{
    public function __construct(
        protected DetectionService $detectionService,
    ) {}

    public function index(Request $request): Response
    {
        $search  = $request->string('search', '')->toString();
        $sortBy  = in_array($request->string('sort_by')->toString(), ['status', 'detected_at', 'created_at'])
                        ? $request->string('sort_by')->toString()
                        : 'created_at';
        $sortDir = in_array($request->string('sort_dir')->toString(), ['asc', 'desc'])
                        ? $request->string('sort_dir')->toString()
                        : 'desc';
        $perPage = min(max($request->integer('per_page', 10), 10), 100);
        $status  = in_array($request->string('status')->toString(), ['all', DetectionStatus::SAFE->value, DetectionStatus::WARNING->value, DetectionStatus::UNSAFE->value])
                        ? $request->string('status')->toString()
                        : 'all';

        $where = [];
        if ($status !== 'all') {
            $where['status'] = $status;
        }

        $detections = $this->detectionService->getPaginatedDetections($search, $sortBy, $sortDir, $perPage, $where);

        return Inertia::render('detection/Master', [
            'detections' => $detections,
            'items'      => Item::orderBy('name')->get(),
            'cameras'    => Camera::with('location')->orderBy('name')->get(),
            'locations'  => Location::orderBy('name')->get(),
            'statuses' => DetectionStatus::cases(),
            'filters'    => [
                'search'   => $search,
                'sort_by'  => $sortBy,
                'sort_dir' => $sortDir,
                'per_page' => $perPage,
                'status'   => $status,
            ],
        ]);
    }

    public function store(StoreDetectionRequest $request): RedirectResponse
    {
        $this->detectionService->createDetection(
            $request->safe()->except('image'),
            $request->file('image'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Detection created.')]);
        return redirect()->route('detection.index');
    }

    public function update(UpdateDetectionRequest $request, Detection $detection): RedirectResponse
    {
        $this->detectionService->updateDetection(
            $detection->id,
            $request->safe()->except('image'),
            $request->file('image'),
        );
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Detection updated.')]);

        return redirect()->route('detection.index');
    }

    public function destroy(Detection $detection): RedirectResponse
    {
        $this->detectionService->deleteDetection($detection->id);
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Detection deleted.')]);
        return redirect()->route('detection.index');
    }

    public function export(Request $request, string $format): BinaryFileResponse
    {
        $format = in_array($format, ['xlsx', 'csv']) ? $format : 'xlsx';

        $search  = $request->string('search', '')->toString();
        $sortBy  = in_array($request->string('sort_by')->toString(), ['status', 'detected_at', 'created_at'])
                        ? $request->string('sort_by')->toString()
                        : 'created_at';
        $sortDir = in_array($request->string('sort_dir')->toString(), ['asc', 'desc'])
                        ? $request->string('sort_dir')->toString()
                        : 'desc';
        $status  = in_array($request->string('status')->toString(), ['all', DetectionStatus::SAFE->value, DetectionStatus::WARNING->value, DetectionStatus::UNSAFE->value])
                        ? $request->string('status')->toString()
                        : 'all';

        $where = [];
        if ($status !== 'all') {
            $where['status'] = $status;
        }

        return Excel::download(
            new DetectionsExport($search, $sortBy, $sortDir, $where),
            'detections.' . $format,
        );
    }
}

