<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cartService)
    {
        // Laravel tự inject CartService qua DI container
    }

    /**
     * Trang giỏ hàng
     */
    public function index()
    {
        $cartItems = $this->cartService->getCartWithProducts();
        $total     = $this->cartService->getTotal();

        return view('pages.cart.index', compact('cartItems', 'total'));
    }

    /**
     * Thêm sản phẩm vào giỏ
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity'   => ['sometimes', 'integer', 'min:1', 'max:99'],
        ]);

        $result = $this->cartService->add(
            productId: (int) $request->product_id,
            quantity:  (int) $request->get('quantity', 1),
        );

        if ($request->expectsJson()) {
            return response()->json([
                ...$result,
                'cart_count' => $this->cartService->getCount(),
            ]);
        }

        if ($result['success']) {
            return back()->with('cart_success', $result['message']);
        }

        return back()->with('cart_error', $result['message']);
    }

    /**
     * Update quantity
     */
    public function update(Request $request, int $productId)
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $result = $this->cartService->update($productId, (int) $request->quantity);

        if ($request->expectsJson()) {
            return response()->json([
                ...$result,
                'cart_count' => $this->cartService->getCount(),
                'total'      => $this->cartService->getTotal(),
            ]);
        }

        return back()->with(
            $result['success'] ? 'cart_success' : 'cart_error',
            $result['message']
        );
    }

    /**
     * Xóa sản phẩm khỏi giỏ
     */
    public function remove(int $productId)
    {
        $result = $this->cartService->remove($productId);

        return back()->with(
            $result['success'] ? 'cart_success' : 'cart_error',
            $result['message']
        );
    }
}