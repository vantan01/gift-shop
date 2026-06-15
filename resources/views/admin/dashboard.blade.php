@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Stats Grid --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach ([
        ['label' => 'Doanh thu',        'value' => number_format($stats['revenue'], 0, ',', '.') . '₫', 'icon' => '💰'],
        ['label' => 'Tổng đơn hàng',    'value' => number_format($stats['totalOrders']),              'icon' => '📦'],
        ['label' => 'Đơn hôm nay',      'value' => $stats['todayOrders'],                             'icon' => '🕐'],
        ['label' => 'Sản phẩm active',  'value' => $stats['activeProducts'],                          'icon' => '🛍️'],
    ] as $stat)
        <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-sm transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <span class="text-2xl">{{ $stat['icon'] }}</span>
            </div>
            <p class="text-xl lg:text-2xl font-bold text-gray-800">{{ $stat['value'] }}</p>
            <p class="text-sm text-gray-500 mt-0.5">{{ $stat['label'] }}</p>
        </div>
    @endforeach
</div>

@if ($stats['lowStockCount'] > 0)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 mb-6 text-sm flex flex-wrap items-center gap-2">
        <span>⚠️</span>
        <span>Có <strong>{{ $stats['lowStockCount'] }}</strong> sản phẩm sắp hết hàng.</span>
        <a href="{{ route('admin.products.index', ['status' => 'active']) }}"
           class="underline font-medium ml-auto">Xem ngay →</a>
    </div>
@endif

{{-- Revenue by month --}}
@if ($revenueByMonth->isNotEmpty())
<div class="bg-white rounded-2xl border border-gray-100 p-5 mb-6">
    <h2 class="font-semibold text-gray-700 mb-4">📈 Doanh thu 6 tháng gần nhất</h2>
    <div class="space-y-3">
        @php $maxRevenue = $revenueByMonth->max('revenue') ?: 1; @endphp
        @foreach ($revenueByMonth as $row)
            <div class="flex items-center gap-3">
                <span class="w-16 text-xs text-gray-500 flex-shrink-0">{{ $row->month }}</span>
                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-pink-400 h-2 rounded-full transition-all"
                         style="width: {{ round(($row->revenue / $maxRevenue) * 100) }}%"></div>
                </div>
                <span class="text-xs font-semibold text-gray-700 w-28 text-right flex-shrink-0">
                    {{ number_format($row->revenue, 0, ',', '.') }}₫
                </span>
                <span class="text-xs text-gray-400 w-16 text-right flex-shrink-0 hidden sm:block">
                    {{ $row->order_count }} đơn
                </span>
            </div>
        @endforeach
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

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
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate">
                                {{ $order->order_number }}
                            </p>
                            <p class="text-xs text-gray-400 truncate">
                                {{ $order->recipient_name }}
                                · {{ $order->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0 ml-3">
                            <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full
                                         {{ $order->status->badgeClasses() }}">
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

{{-- Low stock products --}}
@if ($lowStockProducts->isNotEmpty())
<div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-gray-700">⚠️ Sản phẩm sắp hết hàng</h2>
        <a href="{{ route('admin.products.index') }}"
           class="text-xs text-pink-500 hover:text-pink-600">Quản lý sản phẩm →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                    <th class="pb-2 font-medium">Sản phẩm</th>
                    <th class="pb-2 font-medium">Tồn kho</th>
                    <th class="pb-2 font-medium hidden sm:table-cell">Ngưỡng cảnh báo</th>
                    <th class="pb-2 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($lowStockProducts as $product)
                <tr>
                    <td class="py-2.5 font-medium text-gray-700">{{ $product->name }}</td>
                    <td class="py-2.5">
                        <span class="text-amber-600 font-semibold">{{ $product->stock }}</span>
                    </td>
                    <td class="py-2.5 text-gray-400 hidden sm:table-cell">{{ $product->low_stock_threshold }}</td>
                    <td class="py-2.5 text-right">
                        <a href="{{ route('admin.products.edit', $product) }}"
                           class="text-xs text-pink-500 hover:text-pink-600">Sửa →</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
