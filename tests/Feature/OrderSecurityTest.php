<?php

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Tests\Helpers\CreatesUsers;

uses(CreatesUsers::class);

function createOrderForUser(\App\Models\User $user, string $status = 'pending'): Order
{
    $category = Category::factory()->create(['is_active' => true]);
    $product  = Product::factory()->create([
        'category_id' => $category->id,
        'is_active'   => true,
        'stock'       => 10,
    ]);

    $order = Order::create([
        'user_id'          => $user->id,
        'order_number'     => Order::generateOrderNumber() . '-' . rand(1000, 9999),
        'status'           => $status,
        'recipient_name'   => 'Test User',
        'recipient_phone'  => '0909000000',
        'shipping_address' => '123 Test St',
        'shipping_city'    => 'Hà Nội',
        'subtotal'         => 100000,
        'shipping_fee'     => 30000,
        'discount_amount'  => 0,
        'total'            => 130000,
        'idempotency_key'  => \Illuminate\Support\Str::random(64),
    ]);

    $order->items()->create([
        'product_id'   => $product->id,
        'product_name' => $product->name,
        'unit_price'   => $product->price,
        'quantity'     => 1,
        'subtotal'     => $product->price,
    ]);

    return $order;
}

describe('Order Ownership Security', function () {

    it('user can view their own order', function () {
        $user  = $this->actingAsCustomer();
        $order = createOrderForUser($user);

        $this->get("/orders/{$order->order_number}")->assertOk();
    });

    it('user cannot view another users order', function () {
        $user1 = $this->createCustomer();
        $user2 = $this->createCustomer();

        $order = createOrderForUser($user1);

        // Login là user2
        $this->actingAs($user2);
        $this->get("/orders/{$order->order_number}")->assertNotFound();
    });

    it('guest cannot view any order', function () {
        $user  = $this->createCustomer();
        $order = createOrderForUser($user);

        $this->get("/orders/{$order->order_number}")->assertRedirect('/login');
    });

    it('user can cancel their own PENDING order', function () {
        $user  = $this->actingAsCustomer();
        $order = createOrderForUser($user, 'pending');

        $this->post("/orders/{$order->order_number}/cancel")->assertRedirect();

        $this->assertEquals(
            OrderStatus::CANCELLED->value,
            $order->fresh()->status->value
        );
    });

    it('user cannot cancel SHIPPED order', function () {
        $user  = $this->actingAsCustomer();
        $order = createOrderForUser($user, 'shipped');

        $this->post("/orders/{$order->order_number}/cancel");

        // Status không đổi
        $this->assertEquals(
            OrderStatus::SHIPPED->value,
            $order->fresh()->status->value
        );
    });

    it('user cannot cancel another users order', function () {
        $user1 = $this->createCustomer();
        $user2 = $this->createCustomer();
        $order = createOrderForUser($user1, 'pending');

        $this->actingAs($user2);
        $this->post("/orders/{$order->order_number}/cancel")->assertNotFound();

        // Order của user1 không bị hủy
        $this->assertEquals(
            OrderStatus::PENDING->value,
            $order->fresh()->status->value
        );
    });

    it('stock is restored when order is cancelled', function () {
        $user    = $this->actingAsCustomer();
        $order   = createOrderForUser($user, 'pending');
        $item    = $order->items->first();
        $product = $item->product;

        $stockBefore = $product->stock;

        $this->post("/orders/{$order->order_number}/cancel");

        $stockAfter = $product->fresh()->stock;
        $this->assertEquals($stockBefore + $item->quantity, $stockAfter);
    });

});