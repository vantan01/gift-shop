<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService  $cartService,
        private OrderService $orderService,
    ) {}

    /**
     * Trang checkout
     */
    public function index()
    {
        $cartItems = $this->cartService->getCartWithProducts();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('cart_error', 'Giỏ hàng trống, không thể thanh toán.');
        }

        $subtotal    = $this->cartService->getTotal();
        $shippingFee = $subtotal >= 500000 ? 0 : 30000;
        $total       = $subtotal + $shippingFee;
        $user        = Auth::user();

        // Tạo idempotency key mới mỗi khi vào trang checkout
        $idempotencyKey = hash('sha256', $user->id . '|' . now()->timestamp . '|' . Str::random(16));

        return view('pages.checkout.index', compact(
            'cartItems',
            'subtotal',
            'shippingFee',
            'total',
            'user',
            'idempotencyKey',
        ));
    }

    /**
     * Xử lý đặt hàng
     */
    public function store(CheckoutRequest $request)
    {
        try {
            $order = $this->orderService->createFromCart($request, Auth::user());

            return redirect()
                ->route('orders.show', $order->order_number)
                ->with('order_success', "Đặt hàng thành công! Mã đơn: {$order->order_number}");

        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->with('checkout_error', $e->getMessage());
        }
    }
}