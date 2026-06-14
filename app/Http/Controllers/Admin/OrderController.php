<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
// [Antigravity EDIT - Start] - Import OrderService và DB facade
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
// [Antigravity EDIT - End]

class OrderController extends Controller
{
    // [Antigravity EDIT - Start] - Thêm constructor inject OrderService
    /* Code cũ:
    // Không có constructor
    */
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    // [Antigravity EDIT - End]

    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            // [Antigravity EDIT - Start] - Thay đổi 'ilike' thành operator tương thích DB driver (SQLite vs pgsql)
            /* Code cũ:
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'ilike', "%{$search}%")
                  ->orWhere('recipient_name', 'ilike', "%{$search}%")
                  ->orWhere('recipient_phone', 'ilike', "%{$search}%");
            });
            */
            $driver = DB::connection()->getDriverName();
            $likeOperator = $driver === 'pgsql' ? 'ilike' : 'like';
            
            $query->where(function ($q) use ($search, $likeOperator) {
                $q->where('order_number', $likeOperator, "%{$search}%")
                  ->orWhere('recipient_name', $likeOperator, "%{$search}%")
                  ->orWhere('recipient_phone', $likeOperator, "%{$search}%");
            });
            // [Antigravity EDIT - End]
        }

        $orders   = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $statuses = OrderStatus::cases();

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);
        $statuses = OrderStatus::cases();
        return view('admin.orders.show', compact('order', 'statuses'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status'        => ['required', new Enum(OrderStatus::class)],
            'internal_note' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $order->status->label();
        $newStatus = OrderStatus::from($request->status);

        // [Antigravity EDIT - Start] - Hoàn lại kho bằng OrderService nếu trạng thái đổi sang CANCELLED
        /* Code cũ:
        $order->update([
            'status'        => $newStatus,
            'internal_note' => $request->internal_note ?? $order->internal_note,
        ]);
        */
        if ($newStatus === OrderStatus::CANCELLED && $order->status !== OrderStatus::CANCELLED) {
            $result = $this->orderService->cancel($order);
            if (! $result['success']) {
                return back()->with('error', $result['message']);
            }
            // Cập nhật note nếu có
            if ($request->filled('internal_note')) {
                $order->update(['internal_note' => $request->internal_note]);
            }
        } else {
            $order->update([
                'status'        => $newStatus,
                'internal_note' => $request->internal_note ?? $order->internal_note,
            ]);
        }
        // [Antigravity EDIT - End]

        return back()->with('success',
            "Cập nhật đơn {$order->order_number}: {$oldStatus} → {$newStatus->label()}"
        );
    }
}