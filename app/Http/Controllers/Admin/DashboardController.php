<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index()
    {
        $stats            = $this->dashboardService->getStats();
        $topProducts      = $this->dashboardService->getTopProducts();
        $recentOrders     = $this->dashboardService->getRecentOrders();
        $revenueByMonth   = $this->dashboardService->getRevenueByMonth();
        $lowStockProducts = $this->dashboardService->getLowStockProducts();

        return view('admin.dashboard', compact(
            'stats',
            'topProducts',
            'recentOrders',
            'revenueByMonth',
            'lowStockProducts',
        ));
    }
}