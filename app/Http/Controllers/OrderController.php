<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    /**
     * Danh sách đơn hàng của user đang đăng nhập
     */
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('pages.orders.index', compact('orders'));
    }

    /**
     * Chi tiết đơn hàng
     * Dùng order_number thay vì id — không lộ sequence
     */
    public function show(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())  // QUAN TRỌNG: chỉ xem đơn của mình
            ->with('items.product')
            ->firstOrFail();

        return view('pages.orders.show', compact('order'));
    }

    /**
     * Hủy đơn hàng
     */
    public function cancel(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $result = $this->orderService->cancel($order);

        return back()->with(
            $result['success'] ? 'order_success' : 'order_error',
            $result['message']
        );
    }
}