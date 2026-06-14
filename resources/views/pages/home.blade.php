@extends('layouts.app')

@section('title', 'Trang chủ')

@section('content')

{{-- Hero Section --}}
<section class="bg-gradient-to-br from-pink-50 to-rose-100 py-20">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <p class="text-pink-400 font-medium mb-3 tracking-wide uppercase text-sm">
            Quà tặng yêu thương
        </p>
        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6 leading-tight">
            Gói Trọn Cảm Xúc<br>
            <span class="text-pink-500">Trong Từng Món Quà</span>
        </h1>
        <p class="text-gray-500 text-lg mb-8 max-w-xl mx-auto">
            Thú bông, hoa sáp, hộp quà, chocolate — tất cả được chọn lọc
            để tạo nên những kỷ niệm đáng nhớ.
        </p>
        <a href="/products"
            class="inline-block bg-pink-500 hover:bg-pink-600 text-white
                      font-semibold px-8 py-3 rounded-full transition-colors shadow-md
                      hover:shadow-lg">
            Khám phá ngay ✨
        </a>
    </div>
</section>

{{-- Category Preview --}}
<section class="max-w-6xl mx-auto px-4 py-16">
    <h2 class="text-2xl font-bold text-gray-800 text-center mb-10">
        Danh mục nổi bật
    </h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach ([
        ['emoji' => '🧸', 'name' => 'Thú bông'],
        ['emoji' => '🌸', 'name' => 'Hoa sáp'],
        ['emoji' => '🎁', 'name' => 'Hộp quà'],
        ['emoji' => '🍫', 'name' => 'Chocolate'],
        ] as $category)
        <a href="/products?category={{ Str::slug($category['name']) }}"
            class="bg-white rounded-2xl p-6 text-center shadow-sm
                          hover:shadow-md hover:-translate-y-1 transition-all
                          border border-pink-100 group">
            <div class="text-4xl mb-3">{{ $category['emoji'] }}</div>
            <div class="text-sm font-medium text-gray-700 group-hover:text-pink-500
                                transition-colors">
                {{ $category['name'] }}
            </div>
        </a>
        @endforeach
    </div>
</section>

{{-- Featured Products --}}
<section class="max-w-6xl mx-auto px-4 pb-16">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Sản phẩm nổi bật</h2>
        <a href="/products" class="text-sm text-pink-500 hover:text-pink-600 font-medium">
            Xem tất cả →
        </a>
    </div>
    @if (isset($featuredProducts) && $featuredProducts->isNotEmpty())
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach ($featuredProducts as $product)
        <x-product-card :product="$product" />
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-2xl p-12 text-center border border-pink-100">
        <p class="text-gray-400">Chưa có sản phẩm nổi bật.</p>
    </div>
    @endif
</section>

@endsection