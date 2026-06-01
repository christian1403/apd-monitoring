<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Services\DashboardService;
class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
    ) {}
    
    public function index()
    {
        $dashboardData = $this->dashboardService->getDashboardStats();
        return Inertia::render('Dashboard', $dashboardData);
    }
}