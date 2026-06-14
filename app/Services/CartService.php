<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * Lấy toàn bộ cart từ session
     * Format: ['product_id' => quantity, ...]
     */
    public function getItems(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * Lấy cart kèm thông tin sản phẩm từ DB
     * Đây là nơi backend tự tra giá — không tin session
     */
    public function getCartWithProducts(): Collection
    {
        $items = $this->getItems();

        if (empty($items)) {
            return collect();
        }

        $products = Product::with('category')
            ->active()
            ->whereIn('id', array_keys($items))
            ->get()
            ->keyBy('id');

        return collect($items)
            ->map(function (int $quantity, int $productId) use ($products) {
                $product = $products->get($productId);

                // Sản phẩm bị xóa hoặc ẩn thì bỏ qua
                if (! $product) return null;

                return [
                    'product'   => $product,
                    'quantity'  => $quantity,
                    'subtotal'  => $product->price * $quantity,  // Giá lấy từ DB
                ];
            })
            ->filter(); // Loại bỏ null
    }

    /**
     * Tính tổng tiền — luôn từ DB
     */
    public function getTotal(): int
    {
        return $this->getCartWithProducts()
            ->sum('subtotal');
    }

    /**
     * Đếm tổng số item trong cart (tính theo quantity)
     */
    public function getCount(): int
    {
        return array_sum($this->getItems());
    }

    /**
     * Thêm sản phẩm vào cart
     * Trả về ['success' => bool, 'message' => string]
     */
    public function add(int $productId, int $quantity = 1): array
    {
        // Validate quantity
        if ($quantity < 1 || $quantity > 99) {
            return ['success' => false, 'message' => 'Số lượng không hợp lệ.'];
        }

        // Tra sản phẩm từ DB
        $product = Product::active()->find($productId);

        if (! $product) {
            return ['success' => false, 'message' => 'Sản phẩm không tồn tại.'];
        }

        if ($product->isOutOfStock()) {
            return ['success' => false, 'message' => 'Sản phẩm đã hết hàng.'];
        }

        $items = $this->getItems();
        $currentQty = $items[$productId] ?? 0;
        $newQty = $currentQty + $quantity;

        // Không cho thêm quá số lượng tồn kho
        if ($newQty > $product->stock) {
            return [
                'success' => false,
                'message' => "Chỉ còn {$product->stock} sản phẩm trong kho.",
            ];
        }

        $items[$productId] = $newQty;
        Session::put(self::SESSION_KEY, $items);

        return [
            'success' => true,
            'message' => "Đã thêm \"{$product->name}\" vào giỏ hàng.",
        ];
    }

    /**
     * Cập nhật quantity của một sản phẩm
     */
    public function update(int $productId, int $quantity): array
    {
        if ($quantity < 1) {
            return $this->remove($productId);
        }

        if ($quantity > 99) {
            return ['success' => false, 'message' => 'Số lượng tối đa là 99.'];
        }

        $product = Product::active()->find($productId);

        if (! $product) {
            return ['success' => false, 'message' => 'Sản phẩm không tồn tại.'];
        }

        if ($quantity > $product->stock) {
            return [
                'success' => false,
                'message' => "Chỉ còn {$product->stock} sản phẩm trong kho.",
            ];
        }

        $items = $this->getItems();
        $items[$productId] = $quantity;
        Session::put(self::SESSION_KEY, $items);

        return ['success' => true, 'message' => 'Đã cập nhật giỏ hàng.'];
    }

    /**
     * Xóa một sản phẩm khỏi cart
     */
    public function remove(int $productId): array
    {
        $items = $this->getItems();
        unset($items[$productId]);
        Session::put(self::SESSION_KEY, $items);

        return ['success' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.'];
    }

    /**
     * Xóa toàn bộ cart
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Kiểm tra cart có hợp lệ không trước khi checkout
     * Trả về danh sách lỗi nếu có
     */
    public function validate(): array
    {
        $errors = [];
        $cartItems = $this->getCartWithProducts();

        if ($cartItems->isEmpty()) {
            return ['Giỏ hàng trống.'];
        }

        foreach ($cartItems as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];

            if ($product->isOutOfStock()) {
                $errors[] = "\"{$product->name}\" đã hết hàng.";
            } elseif ($quantity > $product->stock) {
                $errors[] = "\"{$product->name}\" chỉ còn {$product->stock} sản phẩm.";
            }
        }

        return $errors;
    }
}