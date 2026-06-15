@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')
@section('page-title', 'Đơn hàng')

@section('content')

{{-- Filter --}}
<div class="bg-white rounded-2xl border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('admin.orders.index') }}"
          class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Mã đơn, tên, SĐT..."
               class="border border-gray-200 rounded-xl px-3 py-2 text-sm
                      focus:outline-none focus:ring-2 focus:ring-pink-300 flex-1 min-w-40">
        <select name="status"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-pink-300">
            <option value="">Tất cả trạng thái</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}"
                        {{ request('status') === $status->value ? 'selected' : '' }}>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
        <button type="submit"
                class="bg-gray-800 text-white text-sm px-4 py-2 rounded-xl">
            Lọc
        </button>
        <a href="{{ route('admin.orders.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 px-3 py-2">Reset</a>
    </form>
</div>

<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 font-medium text-gray-500">Mã đơn</th>
                <th class="text-left px-5 py-3 font-medium text-gray-500">Khách hàng</th>
                <th class="text-left px-5 py-3 font-medium text-gray-500">Ngày đặt</th>
                <th class="text-right px-5 py-3 font-medium text-gray-500">Tổng tiền</th>
                <th class="text-center px-5 py-3 font-medium text-gray-500">Trạng thái</th>
                <th class="text-right px-5 py-3 font-medium text-gray-500">Chi tiết</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($orders as $order)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $order->order_number }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $order->items->count() }} sản phẩm
                        </p>
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-gray-700">{{ $order->recipient_name }}</p>
                        <p class="text-xs text-gray-400">{{ $order->recipient_phone }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-gray-700">
                        {{ $order->formattedTotal() }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-full
                                     {{ $order->status->badgeClasses() }}">
                            {{ $order->status->label() }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.orders.show', $order) }}"
                           class="text-xs text-pink-500 hover:text-pink-700">
                            Xem →
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                        Không có đơn hàng nào.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($orders->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection