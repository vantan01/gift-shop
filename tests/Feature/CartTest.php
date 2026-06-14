<?php

use App\Models\Category;
use App\Models\Product;
use Tests\Helpers\CreatesUsers;

uses(CreatesUsers::class);

describe('Cart', function () {

    function makeProduct(array $overrides = []): Product
    {
        $category = Category::factory()->create(['is_active' => true]);
        return Product::factory()->create(array_merge([
            'category_id' => $category->id,
            'is_active'   => true,
            'stock'       => 10,
            'price'       => 100000,
        ], $overrides));
    }

    it('can add a product to cart', function () {
        $product = makeProduct();

        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1])
             ->assertRedirect();

        $this->assertEquals(1, session('cart')[$product->id]);
    });

    it('accumulates quantity when adding same product twice', function () {
        $product = makeProduct();

        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 1]);
        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 2]);

        $this->assertEquals(3, session('cart')[$product->id]);
    });

    it('cannot add out of stock product', function () {
        $product = makeProduct(['stock' => 0]);

        $this->post('/cart/add', ['product_id' => $product->id])
             ->assertRedirect();

        $this->assertArrayNotHasKey($product->id, session('cart', []));
    });

    it('cannot add more than available stock', function () {
        $product = makeProduct(['stock' => 3]);

        $this->post('/cart/add', ['product_id' => $product->id, 'quantity' => 5])
             ->assertRedirect();

        $this->assertArrayNotHasKey($product->id, session('cart', []));
    });

    it('can update cart quantity', function () {
        $product = makeProduct();
        session(['cart' => [$product->id => 1]]);

        $this->patch("/cart/{$product->id}", ['quantity' => 3])
             ->assertRedirect();

        $this->assertEquals(3, session('cart')[$product->id]);
    });

    it('removes item when quantity set to 0', function () {
        $product = makeProduct();
        session(['cart' => [$product->id => 2]]);

        $this->patch("/cart/{$product->id}", ['quantity' => 0]);

        $this->assertArrayNotHasKey($product->id, session('cart', []));
    });

    it('can remove a product from cart', function () {
        $product = makeProduct();
        session(['cart' => [$product->id => 2]]);

        $this->delete("/cart/{$product->id}")->assertRedirect();

        $this->assertArrayNotHasKey($product->id, session('cart', []));
    });

    it('shows cart page with correct total from DB', function () {
        // Giá phải lấy từ DB, không từ session
        $product = makeProduct(['price' => 200000]);
        session(['cart' => [$product->id => 2]]);

        $response = $this->get('/cart');
        $total    = $response->viewData('total');

        // 200000 * 2 = 400000
        $this->assertEquals(400000, $total);
    });

    it('rejects invalid product id', function () {
        $this->post('/cart/add', ['product_id' => 99999])
             ->assertRedirect();

        $this->assertEmpty(session('cart', []));
    });

});