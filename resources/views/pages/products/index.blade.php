@extends('layouts.app')

@section('title', $currentCategory ? $currentCategory->name : 'Tất cả sản phẩm')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            {{ $currentCategory ? $currentCategory->name : 'Tất cả sản phẩm' }}
        </h1>
        @if ($currentCategory?->description)
            <p class="text-gray-500 mt-2">{{ $currentCategory->description }}</p>
        @endif
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sidebar Filter --}}
        <aside class="lg:w-56 flex-shrink-0">

            {{-- Category Filter --}}
            <div class="bg-white rounded-2xl border border-pink-100 p-5 mb-4">
                <h3 class="font-semibold text-gray-700 mb-3">Danh mục</h3>
                <ul class="space-y-1">
                    <li>
                        <a href="/products?{{ http_build_query(request()->except(['category', 'page'])) }}"
                           class="block text-sm px-3 py-1.5 rounded-lg transition-colors
                                  {{ ! $currentCategory ? 'bg-pink-100 text-pink-600 font-medium' : 'text-gray-600 hover:text-pink-500' }}">
                            Tất cả
                        </a>
                    </li>
                    @foreach ($categories as $cat)
                        <li>
                            <a href="{{ route('categories.show', $cat) }}"
                               class="block text-sm px-3 py-1.5 rounded-lg transition-colors
                                      {{ $currentCategory?->slug === $cat->slug ? 'bg-pink-100 text-pink-600 font-medium' : 'text-gray-600 hover:text-pink-500' }}">
                                {{ $cat->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Stock Filter --}}
            <div class="bg-white rounded-2xl border border-pink-100 p-5 mb-4">
                <h3 class="font-semibold text-gray-700 mb-3">Tình trạng</h3>
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox"
                           class="accent-pink-500"
                           onchange="applyFilter('in_stock', this.checked ? '1' : '')"
                           {{ request('in_stock') ? 'checked' : '' }}>
                    Còn hàng
                </label>
            </div>

            {{-- Price Filter --}}
            <div class="bg-white rounded-2xl border border-pink-100 p-5">
                <h3 class="font-semibold text-gray-700 mb-3">Khoảng giá</h3>
                <div class="space-y-2">
                    @foreach ([
                        ['label' => 'Dưới 100k',       'min' => '',       'max' => '100000'],
                        ['label' => '100k – 200k',      'min' => '100000', 'max' => '200000'],
                        ['label' => '200k – 350k',      'min' => '200000', 'max' => '350000'],
                        ['label' => 'Trên 350k',        'min' => '350000', 'max' => ''],
                    ] as $range)
                        <a href="/products?{{ http_build_query(array_merge(request()->except(['min_price', 'max_price', 'page']), array_filter(['min_price' => $range['min'], 'max_price' => $range['max']]))) }}"
                           class="block text-sm px-3 py-1.5 rounded-lg transition-colors
                                  {{ request('min_price') == $range['min'] && request('max_price') == $range['max']
                                        ? 'bg-pink-100 text-pink-600 font-medium'
                                        : 'text-gray-600 hover:text-pink-500' }}">
                            {{ $range['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1">

            {{-- Toolbar: search + sort + count --}}
            <div class="flex flex-col sm:flex-row gap-3 mb-6">
                {{-- Search --}}
                <form method="GET" action="/products" class="flex-1">
                    @foreach (request()->except(['search', 'page']) as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Tìm sản phẩm..."
                               class="w-full border border-pink-200 rounded-full px-4 py-2 pr-10
                                      text-sm focus:outline-none focus:ring-2 focus:ring-pink-300">
                        <button type="submit"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-pink-400">
                            🔍
                        </button>
                    </div>
                </form>

                {{-- Sort --}}
                <select onchange="applyFilter('sort', this.value)"
                        class="border border-pink-200 rounded-full px-4 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-pink-300 bg-white">
                    <option value="newest"    {{ $sort === 'newest'    ? 'selected' : '' }}>Mới nhất</option>
                    <option value="popular"   {{ $sort === 'popular'   ? 'selected' : '' }}>Bán chạy</option>
                    <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                    <option value="price_desc"{{ $sort === 'price_desc'? 'selected' : '' }}>Giá giảm dần</option>
                    <option value="name_asc"  {{ $sort === 'name_asc'  ? 'selected' : '' }}>Tên A-Z</option>
                </select>
            </div>

            {{-- Result count --}}
            <p class="text-sm text-gray-400 mb-4">
                Tìm thấy <span class="font-medium text-gray-600">{{ $products->total() }}</span> sản phẩm
            </p>

            {{-- Product Grid --}}
            @if ($products->isEmpty())
                <div class="bg-white rounded-2xl border border-pink-100 p-16 text-center">
                    <div class="text-5xl mb-4">🔍</div>
                    <p class="text-gray-500">Không tìm thấy sản phẩm nào.</p>
                    <a href="/products" class="text-pink-500 text-sm mt-2 inline-block hover:underline">
                        Xem tất cả sản phẩm
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($products->hasPages())
                    <div class="mt-8 flex justify-center">
                        {{ $products->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- JS helper để apply filter mà giữ nguyên params khác --}}
<script>
function applyFilter(key, value) {
    const url = new URL(window.location.href);
    if (value === '' || value === null) {
        url.searchParams.delete(key);
    } else {
        url.searchParams.set(key, value);
    }
    url.searchParams.delete('page'); // Reset về trang 1 khi đổi filter
    window.location.href = url.toString();
}
</script>
@endsection