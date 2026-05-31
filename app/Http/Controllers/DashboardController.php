<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Location;
use App\Models\Camera;
use App\Models\Detection;
use Inertia\Inertia;


class DashboardController extends Controller
{
    public function index()
    {

    $safeCount = Detection::where('status', 'safe')->count();

$violationCount = Detection::whereIn('status', [
    'warning',
    'unsafe'
])->count();

return Inertia::render('Dashboard', [
    'stats' => [
        'items' => Item::count(),
        'locations' => Location::count(),
        'cameras' => Camera::count(),
        'detections' => Detection::count(),
    ],

    'chartData' => [
        'safe' => $safeCount,
        'violation' => $violationCount,
    ],

    'latestDetections' => Detection::with([
        'item',
        'camera',
        'location'
    ])
    ->latest()
    ->take(10)
    ->get(),
]);

        
    }
}