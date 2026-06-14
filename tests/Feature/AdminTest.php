<?php

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Tests\Helpers\CreatesUsers;

uses(CreatesUsers::class);

describe('Admin Product Management', function () {

    it('admin can create a product', function () {
        $this->actingAsAdmin();
        $category = Category::factory()->create(['is_active' => true]);

        $this->post('/admin/products', [
            'category_id'         => $category->id,
            'name'                => 'Sản phẩm test',
            'slug'                => 'san-pham-test',
            'price'               => 199000,
            'stock'               => 20,
            'low_stock_threshold' => 5,
            'is_active'           => true,
            'is_featured'         => false,
        ])->assertRedirect('/admin/products');

        $this->assertDatabaseHas('products', ['slug' => 'san-pham-test']);
    });

    it('admin cannot create product with duplicate slug', function () {
        $this->actingAsAdmin();
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'slug' => 'dup-slug']);

        $this->post('/admin/products', [
            'category_id'         => $category->id,
            'name'                => 'Product 2',
            'slug'                => 'dup-slug',
            'price'               => 100000,
            'stock'               => 5,
            'low_stock_threshold' => 3,
        ])->assertSessionHasErrors('slug');
    });

    it('admin can toggle product visibility', function () {
        $this->actingAsAdmin();
        $category = Category::factory()->create(['is_active' => true]);
        $product  = Product::factory()->create([
            'category_id' => $category->id,
            'is_active'   => true,
        ]);

        $this->patch("/admin/products/{$product->id}/toggle-active");

        $this->assertFalse($product->fresh()->is_active);
    });

    it('admin can soft delete a product', function () {
        $this->actingAsAdmin();
        $category = Category::factory()->create(['is_active' => true]);
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $this->delete("/admin/products/{$product->id}");

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    });

    it('soft deleted product does not show on storefront', function () {
        $this->actingAsAdmin();
        $category = Category::factory()->create(['is_active' => true]);
        $product  = Product::factory()->create([
            'category_id' => $category->id,
            'is_active'   => true,
            'slug'        => 'deleted-product',
        ]);

        $this->delete("/admin/products/{$product->id}");

        // Storefront không tìm thấy
        $this->get('/products/deleted-product')->assertNotFound();
    });

});

describe('Admin Order Management', function () {

    it('admin can update order status', function () {
        $this->actingAsAdmin();

        $customer = $this->createCustomer();
        $category = Category::factory()->create(['is_active' => true]);
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $order = Order::create([
            'user_id'          => $customer->id,
            'order_number'     => 'GS-TEST-0001',
            'status'           => OrderStatus::PENDING,
            'recipient_name'   => 'Test',
            'recipient_phone'  => '0909000000',
            'shipping_address' => '123 Test',
            'shipping_city'    => 'Hà Nội',
            'subtotal'         => 100000,
            'shipping_fee'     => 0,
            'discount_amount'  => 0,
            'total'            => 100000,
            'idempotency_key'  => \Illuminate\Support\Str::random(64),
        ]);

        $this->patch("/admin/orders/{$order->id}/status", [
            'status' => OrderStatus::PACKING->value,
        ]);

        $this->assertEquals(OrderStatus::PACKING->value, $order->fresh()->status->value);
    });

    it('customer cannot update order status via admin route', function () {
        $this->actingAsCustomer();

        $customer = $this->createCustomer();
        $order    = Order::create([
            'user_id'          => $customer->id,
            'order_number'     => 'GS-TEST-0002',
            'status'           => OrderStatus::PENDING,
            'recipient_name'   => 'Test',
            'recipient_phone'  => '0909000000',
            'shipping_address' => '123 Test',
            'shipping_city'    => 'Hà Nội',
            'subtotal'         => 100000,
            'shipping_fee'     => 0,
            'discount_amount'  => 0,
            'total'            => 100000,
            'idempotency_key'  => \Illuminate\Support\Str::random(64),
        ]);

        $this->patch("/admin/orders/{$order->id}/status", [
            'status' => OrderStatus::DELIVERED->value,
        ])->assertForbidden();
    });

});