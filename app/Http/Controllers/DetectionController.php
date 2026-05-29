<?php

namespace App\Http\Controllers;

use App\Models\Camera;
use App\Models\Detection;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DetectionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $detections = Detection::with(['item', 'camera', 'location'])
            ->when($search, function ($query) use ($search) {
                $query->where('status', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('camera', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('ip_address', 'like', "%{$search}%");
                    })
                    ->orWhereHas('location', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('detection/Master', [
            'detections' => $detections,
            'items' => Item::orderBy('name')->get(),
            'cameras' => Camera::with('location')->orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'camera_id' => ['required', 'exists:cameras,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'status' => ['required', 'string', 'in:safe,warning,unsafe'],
            'detected_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $camera = Camera::findOrFail($validated['camera_id']);

        if (empty($validated['location_id'])) {
            $validated['location_id'] = $camera->location_id;
        }

        if (empty($validated['detected_at'])) {
            $validated['detected_at'] = now();
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('detections', 'public');
        }

        Detection::create($validated);

        return redirect()->route('detection.index');
    }

    public function update(Request $request, Detection $detection)
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'camera_id' => ['required', 'exists:cameras,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'status' => ['required', 'string', 'in:safe,warning,unsafe'],
            'detected_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $camera = Camera::findOrFail($validated['camera_id']);

        if (empty($validated['location_id'])) {
            $validated['location_id'] = $camera->location_id;
        }

        if (empty($validated['detected_at'])) {
            $validated['detected_at'] = $detection->detected_at ?? now();
        }

        if ($request->hasFile('image')) {
            if ($detection->image) {
                Storage::disk('public')->delete($detection->image);
            }

            $validated['image'] = $request->file('image')->store('detections', 'public');
        }

        $detection->update($validated);

        return redirect()->route('detection.index');
    }

    public function destroy(Detection $detection)
    {
        if ($detection->image) {
            Storage::disk('public')->delete($detection->image);
        }

        $detection->delete();

        return redirect()->route('detection.index');
    }
}
