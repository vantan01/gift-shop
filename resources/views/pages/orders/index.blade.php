@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

    <h1 class="text-2xl font-bold text-gray-800 mb-8">Đơn hàng của tôi</h1>

    @if (session('order_success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-6 text-sm">
            ✅ {{ session('order_success') }}
        </div>
    @endif

    @if ($orders->isEmpty())
        <div class="bg-white rounded-2xl border border-pink-100 p-12 text-center">
            <div class="text-5xl mb-4">📦</div>
            <p class="text-gray-500 mb-4">Bạn chưa có đơn hàng nào.</p>
            <a href="{{ route('products.index') }}"
               class="inline-block bg-pink-500 hover:bg-pink-600 text-white
                      font-semibold px-6 py-2.5 rounded-full transition-colors">
                Mua sắm ngay
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
                <a href="{{ route('orders.show', $order->order_number) }}"
                   class="block bg-white rounded-2xl border border-pink-100
                          hover:border-pink-300 hover:shadow-md transition-all p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold text-gray-800">
                                {{ $order->order_number }}
                            </p>
                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                                · {{ $order->items->count() }} sản phẩm
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full
                                         bg-{{ $order->status->color() }}-100
                                         text-{{ $order->status->color() }}-700">
                                {{ $order->status->label() }}
                            </span>
                            <p class="text-base font-bold text-pink-600 mt-1">
                                {{ $order->formattedTotal() }}
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection