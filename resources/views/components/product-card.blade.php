@props(['product'])

<div class="group bg-white rounded-2xl overflow-hidden border border-pink-100
            hover:border-pink-300 hover:shadow-lg transition-all duration-200 flex flex-col">

    {{-- Toàn bộ card là link đến product detail --}}
    <a href="{{ route('products.show', $product->slug) }}" class="flex flex-col flex-1">

        {{-- Product Image --}}
        <div class="relative aspect-square bg-pink-50 overflow-hidden">
            @if ($product->image)
                <img src="{{ Str::startsWith($product->image, 'http') ? $product->image : Storage::url($product->image) }}"
                     alt="{{ $product->name }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            @else
                <div class="w-full h-full flex items-center justify-center text-5xl">
                    @switch($product->category->slug)
                        @case('thu-bong')   🧸 @break
                        @case('hoa-sap')    🌸 @break
                        @case('hop-qua')    🎁 @break
                        @case('chocolate')  🍫 @break
                        @case('nen-thom')   🕯️ @break
                        @case('thiep')      💌 @break
                        @default            🎀
                    @endswitch
                </div>
            @endif

            {{-- Badges --}}
            <div class="absolute top-2 left-2 flex flex-col gap-1">
                @if ($product->isOnSale())
                    <span class="bg-rose-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        -{{ $product->discountPercent() }}%
                    </span>
                @endif
                @if ($product->isLowStock())
                    <span class="bg-amber-400 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        Sắp hết
                    </span>
                @endif
                @if ($product->isOutOfStock())
                    <span class="bg-gray-400 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                        Hết hàng
                    </span>
                @endif
            </div>
        </div>

        {{-- Product Info --}}
        <div class="p-4 flex flex-col flex-1">
            <p class="text-xs text-pink-400 font-medium mb-1">
                {{ $product->category->name }}
            </p>
            <h3 class="text-sm font-semibold text-gray-800 mb-1 line-clamp-2
                       group-hover:text-pink-600 transition-colors">
                {{ $product->name }}
            </h3>
            <p class="text-xs text-gray-400 line-clamp-2 mb-3 flex-1">
                {{ $product->short_description }}
            </p>

            {{-- Price --}}
            <div class="flex items-center gap-2 mt-auto">
                <span class="text-base font-bold text-pink-600">
                    {{ $product->formattedPrice() }}
                </span>
                @if ($product->isOnSale())
                    <span class="text-xs text-gray-400 line-through">
                        {{ $product->formattedComparePrice() }}
                    </span>
                @endif
            </div>
        </div>
    </a>

    {{-- Add to cart button — nằm ngoài thẻ <a> để không trigger link --}}
    <div class="px-4 pb-4">
        @if (! $product->isOutOfStock())
            <form method="POST" action="{{ route('cart.add') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit"
                        class="w-full bg-pink-50 hover:bg-pink-500 text-pink-500
                               hover:text-white text-xs font-semibold py-2 rounded-full
                               transition-colors border border-pink-200 hover:border-pink-500">
                    + Thêm vào giỏ
                </button>
            </form>
        @else
            <div class="w-full text-center text-xs text-gray-400 py-2">Hết hàng</div>
        @endif
    </div>
</div>