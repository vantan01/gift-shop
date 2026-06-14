<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getStats(): array
    {
        // Doanh thu từ đơn đã giao (DELIVERED)
        $revenue = Order::where('status', OrderStatus::DELIVERED->value)
            ->sum('total');

        // Tổng số đơn (không tính CANCELLED)
        $totalOrders = Order::whereNot('status', OrderStatus::CANCELLED->value)
            ->count();

        // Đơn mới hôm nay
        $todayOrders = Order::whereDate('created_at', today())->count();

        // Số sản phẩm đang active
        $activeProducts = Product::active()->count();

        // Sản phẩm sắp hết hàng
        $lowStockCount = Product::active()
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->where('stock', '>', 0)
            ->count();

        return compact(
            'revenue',
            'totalOrders',
            'todayOrders',
            'activeProducts',
            'lowStockCount',
        );
    }

    public function getTopProducts(int $limit = 5): \Illuminate\Support\Collection
    {
        return OrderItem::select('product_name', 'product_id',
                                  DB::raw('SUM(quantity) as total_sold'),
                                  DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_name', 'product_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();
    }

    public function getRecentOrders(int $limit = 8): \Illuminate\Support\Collection
    {
        return Order::with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getRevenueByMonth(int $months = 6): \Illuminate\Support\Collection
    {
        // [Antigravity EDIT - Start] - Phân nhánh định dạng ngày tháng tương thích SQLite và pgsql
        /* Code cũ:
        return Order::where('status', OrderStatus::DELIVERED->value)
            ->where('created_at', '>=', now()->subMonths($months))
            ->select(
                DB::raw("TO_CHAR(created_at, 'MM/YYYY') as month"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        */
        $driver = DB::connection()->getDriverName();
        $monthFormat = $driver === 'sqlite'
            ? "strftime('%m/%Y', created_at) as month"
            : "TO_CHAR(created_at, 'MM/YYYY') as month";

        return Order::where('status', OrderStatus::DELIVERED->value)
            ->where('created_at', '>=', now()->subMonths($months))
            ->select(
                DB::raw($monthFormat),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        // [Antigravity EDIT - End]
    }
}