@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">

    <h1 class="text-2xl font-bold text-gray-800 mb-8">🛒 Giỏ hàng</h1>

    {{-- Flash messages --}}
    @if (session('cart_success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-6 text-sm">
            ✅ {{ session('cart_success') }}
        </div>
    @endif
    @if (session('cart_error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 text-sm">
            ⚠️ {{ session('cart_error') }}
        </div>
    @endif

    @if ($cartItems->isEmpty())
        {{-- Empty cart --}}
        <div class="bg-white rounded-2xl border border-pink-100 p-16 text-center">
            <div class="text-6xl mb-4">🛒</div>
            <p class="text-gray-500 text-lg mb-2">Giỏ hàng của bạn đang trống</p>
            <p class="text-gray-400 text-sm mb-6">Hãy thêm những món quà yêu thích vào giỏ nhé!</p>
            <a href="{{ route('products.index') }}"
               class="inline-block bg-pink-500 hover:bg-pink-600 text-white font-semibold
                      px-8 py-3 rounded-full transition-colors">
                Khám phá sản phẩm
            </a>
        </div>

    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Cart Items --}}
            <div class="lg:col-span-2 space-y-3">
                @foreach ($cartItems as $item)
                    @php
                        $product = $item['product'];
                    @endphp
                    <div class="bg-white rounded-2xl border border-pink-100 p-4 flex gap-4 items-start">

                        {{-- Product image --}}
                        <div class="w-20 h-20 flex-shrink-0 bg-pink-50 rounded-xl
                                    flex items-center justify-center text-3xl overflow-hidden">
                            @if ($product->image)
                                <img src="{{ $product->image }}" alt="{{ $product->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                @switch($product->category->slug)
                                    @case('thu-bong')   🧸 @break
                                    @case('hoa-sap')    🌸 @break
                                    @case('hop-qua')    🎁 @break
                                    @case('chocolate')  🍫 @break
                                    @case('nen-thom')   🕯️ @break
                                    @case('thiep')      💌 @break
                                    @default            🎀
                                @endswitch
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('products.show', $product->slug) }}"
                               class="font-semibold text-gray-800 hover:text-pink-600
                                      transition-colors line-clamp-1">
                                {{ $product->name }}
                            </a>
                            <p class="text-xs text-pink-400 mt-0.5">
                                {{ $product->category->name }}
                            </p>
                            <p class="text-sm font-bold text-pink-600 mt-1">
                                {{ $product->formattedPrice() }}
                            </p>

                            {{-- Stock warning --}}
                            @if ($item['quantity'] > $product->stock)
                                <p class="text-xs text-red-500 mt-1">
                                    ⚠️ Chỉ còn {{ $product->stock }} sản phẩm
                                </p>
                            @elseif ($product->isLowStock())
                                <p class="text-xs text-amber-600 mt-1">
                                    Sắp hết hàng
                                </p>
                            @endif
                        </div>

                        {{-- Quantity + Remove --}}
                        <div class="flex flex-col items-end gap-2 flex-shrink-0">
                            {{-- Quantity control --}}
                            <form method="POST"
                                  action="{{ route('cart.update', $product->id) }}"
                                  class="flex items-center border border-pink-200 rounded-full overflow-hidden">
                                @csrf
                                @method('PATCH')

                                <button type="submit" name="quantity"
                                        value="{{ max(0, $item['quantity'] - 1) }}"
                                        class="w-8 h-8 flex items-center justify-center
                                               text-gray-500 hover:text-pink-500 hover:bg-pink-50
                                               transition-colors text-lg leading-none">
                                    −
                                </button>

                                <span class="w-8 text-center text-sm font-medium text-gray-700">
                                    {{ $item['quantity'] }}
                                </span>

                                <button type="submit" name="quantity"
                                        value="{{ $item['quantity'] + 1 }}"
                                        class="w-8 h-8 flex items-center justify-center
                                               text-gray-500 hover:text-pink-500 hover:bg-pink-50
                                               transition-colors text-lg leading-none"
                                        {{ $item['quantity'] >= $product->stock ? 'disabled' : '' }}>
                                    +
                                </button>
                            </form>

                            {{-- Subtotal --}}
                            <p class="text-sm font-semibold text-gray-700">
                                {{ number_format($item['subtotal'], 0, ',', '.') }}₫
                            </p>

                            {{-- Remove --}}
                            <form method="POST"
                                  action="{{ route('cart.remove', $product->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-xs text-gray-400 hover:text-red-500
                                               transition-colors">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Order Summary --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl border border-pink-100 p-6 sticky top-24">
                    <h2 class="font-semibold text-gray-700 mb-5">Tóm tắt đơn hàng</h2>

                    <div class="space-y-3 text-sm">
                        @foreach ($cartItems as $item)
                            <div class="flex justify-between text-gray-500">
                                <span class="line-clamp-1 flex-1 mr-2">
                                    {{ $item['product']->name }}
                                    <span class="text-gray-400">×{{ $item['quantity'] }}</span>
                                </span>
                                <span class="flex-shrink-0">
                                    {{ number_format($item['subtotal'], 0, ',', '.') }}₫
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-pink-100 mt-4 pt-4">
                        <div class="flex justify-between font-bold text-gray-800">
                            <span>Tổng cộng</span>
                            <span class="text-pink-600">
                                {{ number_format($total, 0, ',', '.') }}₫
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Chưa bao gồm phí vận chuyển</p>
                    </div>

                    <a href="/checkout"
                       class="block w-full mt-6 bg-pink-500 hover:bg-pink-600 text-white
                              font-semibold py-3 rounded-full text-center transition-colors">
                        Tiến hành đặt hàng →
                    </a>

                    <a href="{{ route('products.index') }}"
                       class="block w-full mt-3 text-center text-sm text-gray-500
                              hover:text-pink-500 transition-colors">
                        ← Tiếp tục mua sắm
                    </a>
                </div>
            </div>

        </div>
    @endif
</div>
@endsection