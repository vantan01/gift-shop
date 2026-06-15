<?php

use App\Models\Category;
use App\Models\Product;

describe('Product Catalog', function () {

    it('shows product listing page', function () {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->count(3)->create([
            'category_id' => $category->id,
            'is_active'   => true,
        ]);

        $this->get('/products')
             ->assertOk()
             ->assertViewIs('pages.products.index')
             ->assertViewHas('products');
    });

    it('shows only active products', function () {
        $category = Category::factory()->create(['is_active' => true]);

        $active   = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
        $inactive = Product::factory()->create(['category_id' => $category->id, 'is_active' => false]);

        $response = $this->get('/products');

        $response->assertOk();
        $products = $response->viewData('products');

        expect($products->pluck('id'))->toContain($active->id)
            ->not->toContain($inactive->id);
    });

    it('can filter products by category', function () {
        $cat1 = Category::factory()->create(['slug' => 'thu-bong', 'is_active' => true]);
        $cat2 = Category::factory()->create(['slug' => 'hoa-sap',  'is_active' => true]);

        $p1 = Product::factory()->create(['category_id' => $cat1->id, 'is_active' => true]);
        $p2 = Product::factory()->create(['category_id' => $cat2->id, 'is_active' => true]);

        $response = $this->get('/products?category=thu-bong');

        $products = $response->viewData('products');
        expect($products->pluck('id'))->toContain($p1->id)
            ->not->toContain($p2->id);
    });

    it('can search products by name', function () {
        $category = Category::factory()->create(['is_active' => true]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name'        => 'Gấu Teddy Đặc Biệt',
            'is_active'   => true,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name'        => 'Hoa Sáp Hồng',
            'is_active'   => true,
        ]);

        $response  = $this->get('/products?search=Gấu');
        $products  = $response->viewData('products');

        expect($products)->toHaveCount(1);
        expect($products->first()->name)->toBe('Gấu Teddy Đặc Biệt');
    });

    it('shows product detail page', function () {
        $category = Category::factory()->create(['is_active' => true]);
        $product  = Product::factory()->create([
            'category_id' => $category->id,
            'is_active'   => true,
            'slug'        => 'test-product',
        ]);

        $this->get('/products/test-product')
             ->assertOk()
             ->assertViewIs('pages.products.show')
             ->assertViewHas('product', fn($p) => $p->id === $product->id);
    });

    it('returns 404 for inactive product', function () {
        $category = Category::factory()->create(['is_active' => true]);
        $product  = Product::factory()->create([
            'category_id' => $category->id,
            'is_active'   => false,
            'slug'        => 'hidden-product',
        ]);

        $this->get('/products/hidden-product')->assertNotFound();
    });

    it('returns 404 for non-existent product', function () {
        $this->get('/products/does-not-exist')->assertNotFound();
    });

    it('shows category page with products', function () {
        $category = Category::factory()->create([
            'slug'      => 'thu-bong',
            'is_active' => true,
        ]);

        Product::factory()->count(2)->create([
            'category_id' => $category->id,
            'is_active'   => true,
        ]);

        $this->get('/categories/thu-bong')
             ->assertOk()
             ->assertViewIs('pages.categories.show')
             ->assertViewHas('category', fn ($c) => $c->slug === 'thu-bong');
    });

    it('returns 404 for inactive category', function () {
        $category = Category::factory()->create([
            'slug'      => 'hidden-cat',
            'is_active' => false,
        ]);

        $this->get('/categories/hidden-cat')->assertNotFound();
    });

});