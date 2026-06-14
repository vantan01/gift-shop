@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Stats Grid --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ([
        ['label' => 'Doanh thu',        'value' => number_format($stats['revenue'], 0, ',', '.') . '₫', 'icon' => '💰', 'color' => 'pink'],
        ['label' => 'Tổng đơn hàng',    'value' => number_format($stats['totalOrders']),              'icon' => '📦', 'color' => 'blue'],
        ['label' => 'Đơn hôm nay',      'value' => $stats['todayOrders'],                             'icon' => '🕐', 'color' => 'purple'],
        ['label' => 'Sản phẩm active',  'value' => $stats['activeProducts'],                          'icon' => '🛍️', 'color' => 'emerald'],
    ] as $stat)
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-start justify-between mb-3">
                <span class="text-2xl">{{ $stat['icon'] }}</span>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $stat['value'] }}</p>
            <p class="text-sm text-gray-500 mt-0.5">{{ $stat['label'] }}</p>
        </div>
    @endforeach
</div>

@if ($stats['lowStockCount'] > 0)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 mb-6 text-sm flex items-center gap-2">
        <span>⚠️</span>
        <span>Có <strong>{{ $stats['lowStockCount'] }}</strong> sản phẩm sắp hết hàng.</span>
        <a href="{{ route('admin.products.index', ['status' => 'active']) }}"
           class="underline font-medium">Xem ngay</a>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Top Products --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <h2 class="font-semibold text-gray-700 mb-4">🏆 Top sản phẩm bán chạy</h2>
        @if ($topProducts->isEmpty())
            <p class="text-gray-400 text-sm text-center py-6">Chưa có dữ liệu</p>
        @else
            <div class="space-y-3">
                @foreach ($topProducts as $i => $item)
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-pink-100 text-pink-600
                                     text-xs font-bold flex items-center justify-center flex-shrink-0">
                            {{ $i + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate">
                                {{ $item->product_name }}
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-semibold text-gray-700">
                                {{ number_format($item->total_sold) }} đã bán
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ number_format($item->total_revenue, 0, ',', '.') }}₫
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Recent Orders --}}
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-700">📋 Đơn hàng mới nhất</h2>
            <a href="{{ route('admin.orders.index') }}"
               class="text-xs text-pink-500 hover:text-pink-600">Xem tất cả →</a>
        </div>
        @if ($recentOrders->isEmpty())
            <p class="text-gray-400 text-sm text-center py-6">Chưa có đơn hàng nào</p>
        @else
            <div class="space-y-2">
                @foreach ($recentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}"
                       class="flex items-center justify-between py-2 px-3 rounded-xl
                              hover:bg-gray-50 transition-colors">
                        <div>
                            <p class="text-sm font-medium text-gray-700">
                                {{ $order->order_number }}
                            </p>
                            <p class="text-xs text-gray-400">
                                {{ $order->recipient_name }}
                                · {{ $order->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full
                                         bg-{{ $order->status->color() }}-100
                                         text-{{ $order->status->color() }}-700">
                                {{ $order->status->label() }}
                            </span>
                            <p class="text-xs font-semibold text-gray-600 mt-0.5">
                                {{ $order->formattedTotal() }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection