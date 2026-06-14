@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="max-w-6xl mx-auto px-4 py-10">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-400 mb-8 flex items-center gap-2">
        <a href="/" class="hover:text-pink-500">Trang chủ</a>
        <span>/</span>
        <a href="/products" class="hover:text-pink-500">Sản phẩm</a>
        <span>/</span>
        <a href="/products?category={{ $product->category->slug }}"
            class="hover:text-pink-500">{{ $product->category->name }}</a>
        <span>/</span>
        <span class="text-gray-600">{{ $product->name }}</span>
    </nav>

    {{-- Main Product Section --}}
    <div class="bg-white rounded-2xl border border-pink-100 p-6 md:p-10 mb-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">

            {{-- Product Image --}}
            <div class="aspect-square bg-pink-50 rounded-xl overflow-hidden flex items-center justify-center">
                @if ($product->image)
                <img src="{{ $product->image }}"
                    alt="{{ $product->name }}"
                    class="w-full h-full object-cover">
                @else
                <div class="text-8xl">
                    @switch($product->category->slug)
                    @case('thu-bong') 🧸 @break
                    @case('hoa-sap') 🌸 @break
                    @case('hop-qua') 🎁 @break
                    @case('chocolate') 🍫 @break
                    @case('nen-thom') 🕯️ @break
                    @case('thiep') 💌 @break
                    @default 🎀
                    @endswitch
                </div>
                @endif
            </div>

            {{-- Product Info --}}
            <div class="flex flex-col">
                <p class="text-sm text-pink-400 font-medium mb-2">
                    {{ $product->category->name }}
                </p>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-3">
                    {{ $product->name }}
                </h1>

                {{-- Price --}}
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-3xl font-bold text-pink-600">
                        {{ $product->formattedPrice() }}
                    </span>
                    @if ($product->isOnSale())
                    <span class="text-lg text-gray-400 line-through">
                        {{ $product->formattedComparePrice() }}
                    </span>
                    <span class="bg-rose-100 text-rose-600 text-sm font-bold px-2 py-0.5 rounded-full">
                        Tiết kiệm {{ $product->discountPercent() }}%
                    </span>
                    @endif
                </div>

                {{-- Short description --}}
                <p class="text-gray-600 mb-6 leading-relaxed">
                    {{ $product->short_description }}
                </p>

                {{-- Stock status --}}
                <div class="mb-6">
                    @if ($product->isOutOfStock())
                    <span class="inline-flex items-center gap-1.5 text-sm text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                        Hết hàng
                    </span>
                    @elseif ($product->isLowStock())
                    <span class="inline-flex items-center gap-1.5 text-sm text-amber-700 bg-amber-100 px-3 py-1.5 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Chỉ còn {{ $product->stock }} sản phẩm
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 text-sm text-emerald-700 bg-emerald-100 px-3 py-1.5 rounded-full">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Còn hàng
                    </span>
                    @endif
                </div>

                {{-- Flash messages --}}
                @if (session('cart_success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700
                rounded-xl px-4 py-3 mb-4 text-sm">
                    ✅ {{ session('cart_success') }}
                </div>
                @endif
                @if (session('cart_error'))
                <div class="bg-red-50 border border-red-200 text-red-700
                rounded-xl px-4 py-3 mb-4 text-sm">
                    ⚠️ {{ session('cart_error') }}
                </div>
                @endif

                @if (! $product->isOutOfStock())
                <form method="POST" action="{{ route('cart.add') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    {{-- Quantity selector --}}
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-600">Số lượng:</label>
                        <div class="flex items-center border border-pink-200 rounded-full overflow-hidden">
                            <button type="button" id="qty-minus"
                                class="w-9 h-9 flex items-center justify-center text-gray-500
                               hover:text-pink-500 hover:bg-pink-50 transition-colors text-lg">
                                −
                            </button>
                            <input type="number" name="quantity" id="quantity" value="1"
                                min="1" max="{{ $product->stock }}"
                                class="w-12 text-center text-sm font-medium text-gray-700
                              border-none focus:outline-none focus:ring-0">
                            <button type="button" id="qty-plus"
                                class="w-9 h-9 flex items-center justify-center text-gray-500
                               hover:text-pink-500 hover:bg-pink-50 transition-colors text-lg">
                                +
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-pink-500 hover:bg-pink-600 text-white font-semibold
                       py-3 rounded-full transition-colors shadow-md hover:shadow-lg">
                        🛒 Thêm vào giỏ hàng
                    </button>
                </form>
                @else
                <button disabled
                    class="w-full bg-gray-200 text-gray-500 font-semibold
                   py-3 rounded-full cursor-not-allowed">
                    Hết hàng
                </button>
                @endif

                <script>
                    const qtyInput = document.getElementById('quantity');
                    const maxStock  = {{ $product->stock }};

                    // [Antigravity EDIT - Start] - Sử dụng optional chaining tránh crash JS khi phần tử không tồn tại (sản phẩm hết hàng)
                    /* Code cũ:
                    document.getElementById('qty-minus').addEventListener('click', () => {
                        const current = parseInt(qtyInput.value);
                        if (current > 1) qtyInput.value = current - 1;
                    });

                    document.getElementById('qty-plus').addEventListener('click', () => {
                        const current = parseInt(qtyInput.value);
                        if (current < maxStock) qtyInput.value = current + 1;
                    });
                    */
                    document.getElementById('qty-minus')?.addEventListener('click', () => {
                        const current = parseInt(qtyInput.value);
                        if (current > 1) qtyInput.value = current - 1;
                    });

                    document.getElementById('qty-plus')?.addEventListener('click', () => {
                        const current = parseInt(qtyInput.value);
                        if (current < maxStock) qtyInput.value = current + 1;
                    });
                    // [Antigravity EDIT - End]
                </script>
            </div>
        </div>
    </div>

    {{-- Description --}}
    @if ($product->description)
    <div class="bg-white rounded-2xl border border-pink-100 p-6 md:p-10 mb-10">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Mô tả sản phẩm</h2>
        <div class="text-gray-600 leading-relaxed whitespace-pre-line">
            {{ $product->description }}
        </div>
    </div>
    @endif

    {{-- Related Products --}}
    @if ($relatedProducts->isNotEmpty())
    <div>
        <h2 class="text-xl font-bold text-gray-800 mb-6">Sản phẩm liên quan</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($relatedProducts as $related)
            <x-product-card :product="$related" />
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection