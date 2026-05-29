<?php

namespace App\Http\Controllers;

use App\Models\Camera;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CameraController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $cameras = Camera::with('location')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('location', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('camera/Master', [
            'cameras' => $cameras,
            'locations' => Location::orderBy('name')->get(),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ip_address' => ['required', 'string', 'max:255', 'unique:cameras,ip_address'],
            'status' => ['required', 'string', 'in:active,inactive,maintenance'],
            'location_id' => ['required', 'exists:locations,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('cameras', 'public');
        }

        Camera::create($validated);

        return redirect()->route('camera.index');
    }

    public function update(Request $request, Camera $camera)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ip_address' => ['required', 'string', 'max:255', 'unique:cameras,ip_address,' . $camera->id],
            'status' => ['required', 'string', 'in:active,inactive,maintenance'],
            'location_id' => ['required', 'exists:locations,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($camera->image) {
                Storage::disk('public')->delete($camera->image);
            }

            $validated['image'] = $request->file('image')->store('cameras', 'public');
        }

        $camera->update($validated);

        return redirect()->route('camera.index');
    }

    public function destroy(Camera $camera)
    {
        if ($camera->image) {
            Storage::disk('public')->delete($camera->image);
        }

        $camera->delete();

        return redirect()->route('camera.index');
    }
}