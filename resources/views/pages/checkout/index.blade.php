@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">

    <h1 class="text-2xl font-bold text-gray-800 mb-8">Thanh toán</h1>

    {{-- Error --}}
    @if (session('checkout_error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-6 text-sm">
            ⚠️ {{ session('checkout_error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form">
        @csrf

        {{-- Idempotency key ẩn --}}
        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left: Form --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Thông tin người nhận --}}
                <div class="bg-white rounded-2xl border border-pink-100 p-6">
                    <h2 class="font-semibold text-gray-700 mb-4">📦 Thông tin giao hàng</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Tên người nhận <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="recipient_name"
                                   value="{{ old('recipient_name', $user->name) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-pink-300
                                          @error('recipient_name') border-red-300 @enderror">
                            @error('recipient_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Số điện thoại <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="recipient_phone"
                                   value="{{ old('recipient_phone', $user->phone) }}"
                                   placeholder="0909 xxx xxx"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-pink-300
                                          @error('recipient_phone') border-red-300 @enderror">
                            @error('recipient_phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Địa chỉ <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="shipping_address"
                                   value="{{ old('shipping_address', $user->address) }}"
                                   placeholder="Số nhà, tên đường, phường/xã"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-pink-300
                                          @error('shipping_address') border-red-300 @enderror">
                            @error('shipping_address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Tỉnh / Thành phố <span class="text-red-400">*</span>
                            </label>
                            <select name="shipping_city"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-pink-300
                                           @error('shipping_city') border-red-300 @enderror">
                                <option value="">-- Chọn tỉnh/thành --</option>
                                @foreach (['Hà Nội','TP. Hồ Chí Minh','Đà Nẵng','Cần Thơ','Hải Phòng',
                                           'Bình Dương','Đồng Nai','An Giang','Bà Rịa - Vũng Tàu',
                                           'Vĩnh Long','Tiền Giang','Bến Tre','Kiên Giang',
                                           'Khánh Hòa','Lâm Đồng'] as $city)
                                    <option value="{{ $city }}"
                                            {{ old('shipping_city') === $city ? 'selected' : '' }}>
                                        {{ $city }}
                                    </option>
                                @endforeach
                            </select>
                            @error('shipping_city')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Gift options --}}
                <div class="bg-white rounded-2xl border border-pink-100 p-6">
                    <h2 class="font-semibold text-gray-700 mb-4">🎁 Tùy chọn quà tặng</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Lời nhắn gửi kèm
                                <span class="text-gray-400 font-normal">(tối đa 300 ký tự)</span>
                            </label>
                            <textarea name="gift_message" rows="3"
                                      maxlength="300"
                                      placeholder="Viết lời nhắn yêu thương gửi kèm món quà..."
                                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                             focus:outline-none focus:ring-2 focus:ring-pink-300 resize-none
                                             @error('gift_message') border-red-300 @enderror">{{ old('gift_message') }}</textarea>
                            <div class="flex justify-between mt-1">
                                @error('gift_message')
                                    <p class="text-red-500 text-xs">{{ $message }}</p>
                                @else
                                    <span></span>
                                @enderror
                                <span class="text-xs text-gray-400" id="gift-msg-count">0/300</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Ngày giao hàng mong muốn
                                <span class="text-gray-400 font-normal">(tùy chọn)</span>
                            </label>
                            <input type="date" name="scheduled_delivery_date"
                                   value="{{ old('scheduled_delivery_date') }}"
                                   min="{{ now()->addDay()->format('Y-m-d') }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                          focus:outline-none focus:ring-2 focus:ring-pink-300
                                          @error('scheduled_delivery_date') border-red-300 @enderror">
                            @error('scheduled_delivery_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Ghi chú thêm cho shop
                            </label>
                            <textarea name="customer_note" rows="2"
                                      maxlength="500"
                                      placeholder="Ví dụ: giao trước 10h sáng, gọi trước khi giao..."
                                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                             focus:outline-none focus:ring-2 focus:ring-pink-300 resize-none">{{ old('customer_note') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Order Summary --}}
            <div>
                <div class="bg-white rounded-2xl border border-pink-100 p-6 sticky top-24">
                    <h2 class="font-semibold text-gray-700 mb-4">Đơn hàng của bạn</h2>

                    <div class="space-y-2 text-sm mb-4">
                        @foreach ($cartItems as $item)
                            <div class="flex justify-between text-gray-600">
                                <span class="line-clamp-1 flex-1 mr-2">
                                    {{ $item['product']->name }}
                                    <span class="text-gray-400">×{{ $item['quantity'] }}</span>
                                </span>
                                <span class="flex-shrink-0 font-medium">
                                    {{ number_format($item['subtotal'], 0, ',', '.') }}₫
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-pink-100 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Tạm tính</span>
                            <span>{{ number_format($subtotal, 0, ',', '.') }}₫</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Phí vận chuyển</span>
                            <span>
                                @if ($shippingFee === 0)
                                    <span class="text-emerald-600 font-medium">Miễn phí</span>
                                @else
                                    {{ number_format($shippingFee, 0, ',', '.') }}₫
                                @endif
                            </span>
                        </div>
                        @if ($shippingFee > 0)
                            <p class="text-xs text-gray-400">
                                Miễn phí ship cho đơn từ 500.000₫
                            </p>
                        @endif
                    </div>

                    <div class="border-t border-pink-100 mt-4 pt-4">
                        <div class="flex justify-between font-bold text-gray-800 text-base">
                            <span>Tổng cộng</span>
                            <span class="text-pink-600">{{ number_format($total, 0, ',', '.') }}₫</span>
                        </div>
                    </div>

                    <button type="submit" id="submit-btn"
                            class="w-full mt-6 bg-pink-500 hover:bg-pink-600 text-white
                                   font-semibold py-3 rounded-full transition-colors
                                   disabled:opacity-60 disabled:cursor-not-allowed">
                        Đặt hàng ngay 💝
                    </button>

                    <p class="text-xs text-gray-400 text-center mt-3">
                        Thanh toán khi nhận hàng (COD)
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Đếm ký tự gift message
    const giftMsg   = document.querySelector('[name="gift_message"]');
    const msgCount  = document.getElementById('gift-msg-count');
    if (giftMsg) {
        giftMsg.addEventListener('input', () => {
            msgCount.textContent = giftMsg.value.length + '/300';
        });
    }

    // Chặn double submit
    const form      = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('submit-btn');
    if (form) {
        form.addEventListener('submit', () => {
            submitBtn.disabled    = true;
            submitBtn.textContent = 'Đang xử lý...';
        });
    }
</script>
@endsection