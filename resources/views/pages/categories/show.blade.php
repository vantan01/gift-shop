@extends('layouts.app')

@section('title', $category->name)

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-400 mb-6 flex items-center gap-2 flex-wrap">
        <a href="{{ route('home') }}" class="hover:text-pink-500 transition-colors">Trang chủ</a>
        <span>/</span>
        <a href="{{ route('products.index') }}" class="hover:text-pink-500 transition-colors">Sản phẩm</a>
        <span>/</span>
        <span class="text-gray-600 font-medium">{{ $category->name }}</span>
    </nav>

    {{-- Category Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-8">
        <div class="w-16 h-16 rounded-2xl bg-pink-50 border border-pink-100
                    flex items-center justify-center text-3xl flex-shrink-0">
            {{ $category->emoji() }}
        </div>
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="text-gray-500 mt-1">{{ $category->description }}</p>
            @endif
            <p class="text-sm text-gray-400 mt-1">{{ $products->total() }} sản phẩm</p>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar: other categories --}}
        @if (isset($navCategories) && $navCategories->isNotEmpty())
        <aside class="lg:w-56 flex-shrink-0">
            <div class="bg-white rounded-2xl border border-pink-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-3">Danh mục khác</h3>
                <ul class="space-y-1">
                    @foreach ($navCategories as $cat)
                        <li>
                            <a href="{{ route('categories.show', $cat) }}"
                               class="flex items-center gap-2 text-sm px-3 py-1.5 rounded-lg transition-colors
                                      {{ $cat->id === $category->id
                                          ? 'bg-pink-100 text-pink-600 font-medium'
                                          : 'text-gray-600 hover:text-pink-500 hover:bg-pink-50' }}">
                                <span>{{ $cat->emoji() }}</span>
                                {{ $cat->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
        @endif

        {{-- Product Grid --}}
        <div class="flex-1 min-w-0">
            {{-- Sort bar --}}
            <div class="flex items-center justify-between mb-6">
                <p class="text-sm text-gray-500">
                    Hiển thị {{ $products->count() }} / {{ $products->total() }} sản phẩm
                </p>
                <form method="GET" class="flex items-center gap-2">
                    <label class="text-sm text-gray-500">Sắp xếp:</label>
                    <select name="sort" onchange="this.form.submit()"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-1.5
                                   focus:outline-none focus:ring-2 focus:ring-pink-300">
                        <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Giá thấp → cao</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Giá cao → thấp</option>
                    </select>
                </form>
            </div>

            @if ($products->isNotEmpty())
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @else
                <div class="bg-white rounded-2xl p-12 text-center border border-pink-100">
                    <p class="text-4xl mb-3">{{ $category->emoji() }}</p>
                    <p class="text-gray-500 font-medium">Chưa có sản phẩm trong danh mục này</p>
                    <a href="{{ route('products.index') }}"
                       class="inline-block mt-4 text-sm text-pink-500 hover:text-pink-600 font-medium">
                        Xem tất cả sản phẩm →
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
