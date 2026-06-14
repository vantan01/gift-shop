<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Http\Requests\CheckoutRequest;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private CartService $cartService) {}

    /**
     * Tạo đơn hàng từ cart
     * Dùng DB transaction để đảm bảo tính toàn vẹn
     */
    public function createFromCart(CheckoutRequest $request, User $user): Order
    {
        // 1. Validate cart trước
        $errors = $this->cartService->validate();
        if (! empty($errors)) {
            throw new \RuntimeException(implode(' ', $errors));
        }

        // 2. Kiểm tra idempotency — tránh tạo đơn trùng
        $existingOrder = Order::where('idempotency_key', $request->idempotency_key)
            ->where('user_id', $user->id)
            ->first();

        if ($existingOrder) {
            return $existingOrder; // Trả về đơn cũ, không tạo mới
        }

        // 3. Lấy cart items với giá từ DB
        $cartItems = $this->cartService->getCartWithProducts();

        // 4. Backend tự tính subtotal — không dùng bất kỳ số tiền nào từ request
        $subtotal    = $cartItems->sum('subtotal');
        $shippingFee = $this->calculateShippingFee($subtotal);
        $total       = $subtotal + $shippingFee;

        // 5. Bọc trong transaction
        return DB::transaction(function () use (
            $request, $user, $cartItems, $subtotal, $shippingFee, $total
        ) {
            // Tạo order
            $order = Order::create([
                'user_id'                  => $user->id,
                'order_number'             => Order::generateOrderNumber(),
                'status'                   => OrderStatus::PENDING,
                'recipient_name'           => $request->recipient_name,
                'recipient_phone'          => $request->recipient_phone,
                'shipping_address'         => $request->shipping_address,
                'shipping_city'            => $request->shipping_city,
                'gift_message'             => $request->gift_message,
                'scheduled_delivery_date'  => $request->scheduled_delivery_date,
                'customer_note'            => $request->customer_note,
                'subtotal'                 => $subtotal,
                'shipping_fee'             => $shippingFee,
                'discount_amount'          => 0,
                'total'                    => $total,
                'idempotency_key'          => $request->idempotency_key,
            ]);

            // Tạo order items + trừ stock
            foreach ($cartItems as $item) {
                $product = $item['product'];

                // Tạo snapshot — lưu giá và tên tại thời điểm này
                $order->items()->create([
                    'product_id'   => $product->id,
                    'product_name' => $product->name,   // Snapshot
                    'unit_price'   => $product->price,  // Snapshot giá từ DB
                    'quantity'     => $item['quantity'],
                    'subtotal'     => $product->price * $item['quantity'],
                ]);

                // Trừ stock an toàn bằng atomic decrement
                // Kiểm tra đồng thời stock >= quantity để tránh oversell
                $updated = \App\Models\Product::where('id', $product->id)
                    ->where('stock', '>=', $item['quantity'])
                    ->decrement('stock', $item['quantity']);

                if (! $updated) {
                    // Không cập nhật được = đã hết hàng (race condition)
                    throw new \RuntimeException(
                        "Sản phẩm \"{$product->name}\" vừa hết hàng. Vui lòng cập nhật giỏ hàng."
                    );
                }

                // Tăng sold_count
                $product->increment('sold_count', $item['quantity']);
            }

            // Xóa cart sau khi đặt hàng thành công
            $this->cartService->clear();

            return $order;
        });
    }

    /**
     * Hủy đơn hàng
     */
    public function cancel(Order $order): array
    {
        if (! $order->isCancellable()) {
            return [
                'success' => false,
                'message' => 'Không thể hủy đơn hàng ở trạng thái ' . $order->status->label() . '.',
            ];
        }

        DB::transaction(function () use ($order) {
            // Hoàn lại stock
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    \App\Models\Product::where('id', $item->product_id)
                        ->increment('stock', $item->quantity);

                    \App\Models\Product::where('id', $item->product_id)
                        ->decrement('sold_count', $item->quantity);
                }
            }

            $order->update(['status' => OrderStatus::CANCELLED]);
        });

        return ['success' => true, 'message' => 'Đơn hàng đã được hủy.'];
    }

    /**
     * Tính phí vận chuyển đơn giản
     */
    private function calculateShippingFee(int $subtotal): int
    {
        // Miễn phí ship khi đơn >= 500k
        if ($subtotal >= 500000) return 0;
        return 30000;
    }
}