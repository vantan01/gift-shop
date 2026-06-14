<?php

use Tests\Helpers\CreatesUsers;

uses(CreatesUsers::class);

describe('Authorization', function () {

    // --- Admin routes ---

    it('blocks guest from admin routes', function () {
        $this->get('/admin')->assertRedirect('/login');
    });

    it('blocks customer from admin routes', function () {
        $this->actingAsCustomer();
        $this->get('/admin')->assertForbidden();
    });

    it('allows admin to access admin dashboard', function () {
        $this->actingAsAdmin();
        $this->get('/admin')->assertOk();
    });

    it('blocks customer from admin product management', function () {
        $this->actingAsCustomer();
        $this->get('/admin/products')->assertForbidden();
    });

    it('blocks customer from admin order management', function () {
        $this->actingAsCustomer();
        $this->get('/admin/orders')->assertForbidden();
    });

    it('allows admin to access product management', function () {
        $this->actingAsAdmin();
        $this->get('/admin/products')->assertOk();
    });

    // --- Customer routes ---

    it('blocks guest from checkout', function () {
        $this->get('/checkout')->assertRedirect('/login');
    });

    it('blocks guest from orders page', function () {
        $this->get('/orders')->assertRedirect('/login');
    });

    it('allows customer to access checkout', function () {
        // Cần có item trong cart trước
        $this->actingAsCustomer();

        $category = \App\Models\Category::factory()->create(['is_active' => true]);
        $product  = \App\Models\Product::factory()->create([
            'category_id' => $category->id,
            'is_active'   => true,
            'stock'       => 10,
        ]);

        // Add to cart via session
        session(['cart' => [$product->id => 1]]);

        $this->get('/checkout')->assertOk();
    });

});
