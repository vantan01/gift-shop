<?php

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesUsers;

uses(CreatesUsers::class);

function makeActiveProduct(int $stock = 10, int $price = 100000): Product
{
    $category = Category::factory()->create(['is_active' => true]);
    return Product::factory()->create([
        'category_id' => $category->id,
        'is_active'   => true,
        'stock'       => $stock,
        'price'       => $price,
    ]);
}

function checkoutPayload(array $overrides = []): array
{
    return array_merge([
        'recipient_name'  => 'Nguyễn Thị Test',
        'recipient_phone' => '0909123456',
        'shipping_address'=> '123 Đường Test',
        'shipping_city'   => 'Hà Nội',
        'gift_message'    => null,
        'idempotency_key' => hash('sha256', Str::random(32)),
    ], $overrides);
}

describe('Checkout', function () {

    it('requires authentication', function () {
        $this->post('/checkout', checkoutPayload())->assertRedirect('/login');
    });

    it('creates order and clears cart', function () {
        $user    = $this->actingAsCustomer();
        $product = makeActiveProduct(stock: 10, price: 150000);

        session(['cart' => [$product->id => 2]]);

        $this->post('/checkout', checkoutPayload())->assertRedirect();

        // Order được tạo
        $this->assertDatabaseHas('orders', [
            'user_id'          => $user->id,
            'recipient_name'   => 'Nguyễn Thị Test',
            'status'           => OrderStatus::PENDING->value,
        ]);

        // Cart bị xóa
        $this->assertEmpty(session('cart', []));
    });

    it('backend calculates total from DB not frontend', function () {
        $user    = $this->actingAsCustomer();
        $product = makeActiveProduct(price: 200000);
        session(['cart' => [$product->id => 2]]);

        // Thử gửi thêm field total giả (backend phải bỏ qua)
        $payload = array_merge(checkoutPayload(), ['total' => 1]);
        $this->post('/checkout', $payload);

        $order = Order::where('user_id', $user->id)->first();

        // Total phải là 200000 * 2 + shipping, không phải 1
        $this->assertGreaterThan(100, $order->total);
        $this->assertEquals(400000, $order->subtotal);
    });

    it('saves product name and price snapshot in order items', function () {
        $user    = $this->actingAsCustomer();
        $product = makeActiveProduct(price: 199000);
        $product->update(['name' => 'Gấu Teddy Test']);
        session(['cart' => [$product->id => 1]]);

        $this->post('/checkout', checkoutPayload());

        $order = Order::where('user_id', $user->id)->first();
        $item  = $order->items->first();

        $this->assertEquals('Gấu Teddy Test', $item->product_name);
        $this->assertEquals(199000, $item->unit_price);
    });

    it('decrements stock after successful checkout', function () {
        $user    = $this->actingAsCustomer();
        $product = makeActiveProduct(stock: 10);
        session(['cart' => [$product->id => 3]]);

        $this->post('/checkout', checkoutPayload());

        $this->assertEquals(7, $product->fresh()->stock);
    });

    it('prevents creating duplicate order with same idempotency key', function () {
        $user    = $this->actingAsCustomer();
        $product = makeActiveProduct(stock: 20);
        $key     = hash('sha256', Str::random(32));

        // Submit lần 1
        session(['cart' => [$product->id => 1]]);
        $this->post('/checkout', checkoutPayload(['idempotency_key' => $key]));

        // Submit lần 2 với cùng key
        session(['cart' => [$product->id => 1]]);
        $this->post('/checkout', checkoutPayload(['idempotency_key' => $key]));

        $count = Order::where('user_id', $user->id)->count();
        $this->assertEquals(1, $count);
    });

    it('rejects checkout with empty cart', function () {
        $this->actingAsCustomer();
        session(['cart' => []]);

        $this->post('/checkout', checkoutPayload())->assertRedirect('/cart');
    });

    it('validates required fields', function () {
        $user    = $this->actingAsCustomer();
        $product = makeActiveProduct();
        session(['cart' => [$product->id => 1]]);

        $this->post('/checkout', [
            'recipient_name'  => '',
            'recipient_phone' => '',
            'shipping_address'=> '',
            'shipping_city'   => '',
            'idempotency_key' => hash('sha256', Str::random(32)),
        ])->assertSessionHasErrors(['recipient_name', 'recipient_phone', 'shipping_address', 'shipping_city']);
    });

    it('rejects scheduled delivery in the past', function () {
        $user    = $this->actingAsCustomer();
        $product = makeActiveProduct();
        session(['cart' => [$product->id => 1]]);

        $this->post('/checkout', checkoutPayload([
            'scheduled_delivery_date' => now()->subDay()->format('Y-m-d'),
        ]))->assertSessionHasErrors('scheduled_delivery_date');
    });

    it('rejects gift message exceeding 300 chars', function () {
        $user    = $this->actingAsCustomer();
        $product = makeActiveProduct();
        session(['cart' => [$product->id => 1]]);

        $this->post('/checkout', checkoutPayload([
            'gift_message' => str_repeat('a', 301),
        ]))->assertSessionHasErrors('gift_message');
    });

});