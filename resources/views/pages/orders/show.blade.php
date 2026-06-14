@extends('layouts.app')

@section('title', 'Đơn hàng ' . $order->order_number)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

    {{-- Messages --}}
    @if (session('order_success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-6 text-sm">
            ✅ {{ session('order_success') }}
        </div>
    @endif
    @if (session('order_error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 text-sm">
            ⚠️ {{ session('order_error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-start justify-between mb-8 gap-4">
        <div>
            <p class="text-sm text-gray-400 mb-1">Mã đơn hàng</p>
            <h1 class="text-xl font-bold text-gray-800">{{ $order->order_number }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                Đặt lúc {{ $order->created_at->format('H:i · d/m/Y') }}
            </p>
        </div>
        <span class="inline-block text-sm font-semibold px-4 py-1.5 rounded-full flex-shrink-0
                     bg-{{ $order->status->color() }}-100 text-{{ $order->status->color() }}-700">
            {{ $order->status->label() }}
        </span>
    </div>

    {{-- Order Timeline --}}
    @if ($order->status !== \App\Enums\OrderStatus::CANCELLED)
        <div class="bg-white rounded-2xl border border-pink-100 p-6 mb-5">
            <h2 class="font-semibold text-gray-700 mb-4">Trạng thái đơn hàng</h2>
            <div class="flex items-center">
                @foreach ([\App\Enums\OrderStatus::PENDING, \App\Enums\OrderStatus::PAID, \App\Enums\OrderStatus::PACKING, \App\Enums\OrderStatus::SHIPPED, \App\Enums\OrderStatus::DELIVERED] as $step)
                    @php
                        $isCompleted = $order->status->step() >= $step->step();
                        $isCurrent   = $order->status === $step;
                    @endphp
                    <div class="flex items-center {{ ! $loop->last ? 'flex-1' : '' }}">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                        {{ $isCompleted ? 'bg-pink-500 text-white' : 'bg-gray-100 text-gray-400' }}
                                        {{ $isCurrent ? 'ring-2 ring-pink-300 ring-offset-2' : '' }}">
                                {{ $isCompleted ? '✓' : $loop->iteration }}
                            </div>
                            <p class="text-[10px] text-center mt-1 w-16
                                      {{ $isCompleted ? 'text-pink-600 font-medium' : 'text-gray-400' }}">
                                {{ $step->label() }}
                            </p>
                        </div>
                        @if (! $loop->last)
                            <div class="flex-1 h-0.5 mx-1 mb-4
                                        {{ $order->status->step() > $step->step() ? 'bg-pink-400' : 'bg-gray-200' }}">
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Order Items --}}
    <div class="bg-white rounded-2xl border border-pink-100 p-6 mb-5">
        <h2 class="font-semibold text-gray-700 mb-4">Sản phẩm</h2>
        <div class="space-y-3">
            @foreach ($order->items as $item)
                <div class="flex items-center gap-4 py-2
                            {{ ! $loop->last ? 'border-b border-pink-50' : '' }}">
                    <div class="w-12 h-12 bg-pink-50 rounded-lg flex items-center
                                justify-center text-2xl flex-shrink-0">
                        @if ($item->product?->image)
                            {{-- [Antigravity EDIT - Start] - Null safe image access
                            Code cũ:
                            <img src="{{ $item->product->image }}" class="w-full h-full object-cover rounded-lg">
                            --}}
                            <img src="{{ $item->product?->image }}" class="w-full h-full object-cover rounded-lg">
                            {{-- [Antigravity EDIT - End] --}}
                        @else
                            🎁
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        {{-- Dùng snapshot name — không dùng $item->product->name --}}
                        <p class="text-sm font-medium text-gray-800 line-clamp-1">
                            {{ $item->product_name }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $item->formattedUnitPrice() }} × {{ $item->quantity }}
                        </p>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 flex-shrink-0">
                        {{ $item->formattedSubtotal() }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Totals --}}
        <div class="border-t border-pink-100 mt-4 pt-4 space-y-2 text-sm">
            <div class="flex justify-between text-gray-500">
                <span>Tạm tính</span>
                <span>{{ $order->formattedSubtotal() }}</span>
            </div>
            <div class="flex justify-between text-gray-500">
                <span>Phí vận chuyển</span>
                <span>
                    @if ($order->shipping_fee === 0)
                        <span class="text-emerald-600">Miễn phí</span>
                    @else
                        {{ number_format($order->shipping_fee, 0, ',', '.') }}₫
                    @endif
                </span>
            </div>
            <div class="flex justify-between font-bold text-gray-800 text-base pt-1">
                <span>Tổng cộng</span>
                <span class="text-pink-600">{{ $order->formattedTotal() }}</span>
            </div>
        </div>
    </div>

    {{-- Shipping Info --}}
    <div class="bg-white rounded-2xl border border-pink-100 p-6 mb-5">
        <h2 class="font-semibold text-gray-700 mb-3">Thông tin giao hàng</h2>
        <div class="text-sm text-gray-600 space-y-1.5">
            <p><span class="text-gray-400">Người nhận:</span>
               <span class="font-medium ml-1">{{ $order->recipient_name }}</span></p>
            <p><span class="text-gray-400">Điện thoại:</span>
               <span class="ml-1">{{ $order->recipient_phone }}</span></p>
            <p><span class="text-gray-400">Địa chỉ:</span>
               <span class="ml-1">{{ $order->shipping_address }}, {{ $order->shipping_city }}</span></p>
            @if ($order->scheduled_delivery_date)
                <p><span class="text-gray-400">Ngày giao:</span>
                   <span class="ml-1">{{ $order->scheduled_delivery_date->format('d/m/Y') }}</span></p>
            @endif
            @if ($order->gift_message)
                <div class="mt-3 bg-pink-50 rounded-xl p-3">
                    <p class="text-xs text-pink-400 font-medium mb-1">💌 Lời nhắn</p>
                    <p class="text-gray-700 italic">"{{ $order->gift_message }}"</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('orders.index') }}"
           class="text-sm text-gray-500 hover:text-pink-500 transition-colors">
            ← Tất cả đơn hàng
        </a>

        @if ($order->isCancellable())
            <form method="POST" action="{{ route('orders.cancel', $order->order_number) }}"
                  onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                @csrf
                <button type="submit"
                        class="text-sm text-red-500 hover:text-red-600 font-medium
                               border border-red-200 hover:border-red-400 px-4 py-2
                               rounded-full transition-colors">
                    Hủy đơn hàng
                </button>
            </form>
        @endif
    </div>
</div>
@endsection