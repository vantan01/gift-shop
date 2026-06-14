@extends('layouts.admin')

@section('title', 'Đơn ' . $order->order_number)
@section('page-title', $order->order_number)
@section('breadcrumb', 'Admin › Đơn hàng › ' . $order->order_number)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Items --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-700 mb-4">Sản phẩm</h2>
            <div class="space-y-3">
                @foreach ($order->items as $item)
                    <div class="flex items-center gap-3 py-2
                                {{ ! $loop->last ? 'border-b border-gray-50' : '' }}">
                        <div class="w-10 h-10 bg-pink-50 rounded-lg flex items-center
                                    justify-center text-xl flex-shrink-0">🎁</div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $item->formattedUnitPrice() }} × {{ $item->quantity }}
                            </p>
                        </div>
                        <p class="text-sm font-semibold text-gray-700">
                            {{ $item->formattedSubtotal() }}
                        </p>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-gray-100 mt-4 pt-4 space-y-1.5 text-sm">
                <div class="flex justify-between text-gray-500">
                    <span>Tạm tính</span><span>{{ $order->formattedSubtotal() }}</span>
                </div>
                <div class="flex justify-between text-gray-500">
                    <span>Vận chuyển</span>
                    <span>{{ $order->shipping_fee ? number_format($order->shipping_fee, 0, ',', '.') . '₫' : 'Miễn phí' }}</span>
                </div>
                <div class="flex justify-between font-bold text-gray-800 text-base pt-1">
                    <span>Tổng cộng</span>
                    <span class="text-pink-600">{{ $order->formattedTotal() }}</span>
                </div>
            </div>
        </div>

        {{-- Shipping --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-700 mb-3">Thông tin giao hàng</h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Người nhận</p>
                    <p class="font-medium text-gray-700">{{ $order->recipient_name }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs mb-0.5">Điện thoại</p>
                    <p class="text-gray-700">{{ $order->recipient_phone }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-gray-400 text-xs mb-0.5">Địa chỉ</p>
                    <p class="text-gray-700">
                        {{ $order->shipping_address }}, {{ $order->shipping_city }}
                    </p>
                </div>
                @if ($order->gift_message)
                    <div class="col-span-2">
                        <p class="text-gray-400 text-xs mb-0.5">Lời nhắn</p>
                        <p class="text-gray-700 italic bg-pink-50 rounded-lg p-2 text-xs">
                            "{{ $order->gift_message }}"
                        </p>
                    </div>
                @endif
                @if ($order->customer_note)
                    <div class="col-span-2">
                        <p class="text-gray-400 text-xs mb-0.5">Ghi chú khách hàng</p>
                        <p class="text-gray-700 text-xs">{{ $order->customer_note }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Update status --}}
    <div class="space-y-5">

        {{-- Current status --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-700 mb-3">Trạng thái hiện tại</h2>
            <span class="inline-block text-sm font-semibold px-3 py-1.5 rounded-full
                         bg-{{ $order->status->color() }}-100
                         text-{{ $order->status->color() }}-700">
                {{ $order->status->label() }}
            </span>
        </div>

        {{-- Update status form --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <h2 class="font-semibold text-gray-700 mb-3">Cập nhật trạng thái</h2>
            <form method="POST"
                  action="{{ route('admin.orders.update-status', $order) }}">
                @csrf
                @method('PATCH')

                <div class="space-y-3">
                    <select name="status"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-pink-300">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}"
                                    {{ $order->status === $status ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>

                    <textarea name="internal_note"
                              rows="3"
                              placeholder="Ghi chú nội bộ (không hiển thị với khách)..."
                              class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm
                                     focus:outline-none focus:ring-2 focus:ring-pink-300 resize-none">{{ $order->internal_note }}</textarea>

                    <button type="submit"
                            class="w-full bg-pink-500 hover:bg-pink-600 text-white font-semibold
                                   py-2.5 rounded-xl transition-colors text-sm">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>

        {{-- Customer info --}}
        @if ($order->user)
            <div class="bg-white rounded-2xl border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-700 mb-3">Khách hàng</h2>
                <p class="text-sm font-medium text-gray-700">{{ $order->user->name }}</p>
                <p class="text-xs text-gray-400">{{ $order->user->email }}</p>
            </div>
        @endif
    </div>
</div>
@endsection