<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\GetDashboardDataRequest;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(GetDashboardDataRequest $request)
    {
        $user = $request->user();
        $days = $request->getDays();
        $limit = $request->getLimit();

        // Check if user has vendor profile (from database, not just session)
        $isVendor = $user->role === 'vendor' && $user->vendor()->exists();

        return inertia('Dashboard', [
            'stats' => $this->dashboardService->getUserStats($user),
            'recentActivities' => $this->dashboardService->getRecentActivities($user, $limit),
            'quickActions' => $this->dashboardService->getQuickActions($user),
            'chartData' => $this->dashboardService->getChartData($user, $days),
            'isVendor' => $isVendor, // Pass vendor status from database
        ]);
    }
}
