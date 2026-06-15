@extends('layouts.app')

@section('title', 'Xác thực Email')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <span class="text-4xl">📧</span>
            <h1 class="text-2xl font-bold text-gray-800 mt-3">Xác thực Email</h1>
            <p class="text-gray-500 text-sm mt-2">
                Cảm ơn bạn đã đăng ký! Vui lòng kiểm tra hộp thư đến và nhấn vào liên kết xác thực để kích hoạt tài khoản. Nếu bạn không nhận được email, chúng tôi có thể gửi lại.
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-pink-100 p-8">

            @if (session('status') == 'verification-link-sent')
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-6 text-sm">
                    Một liên kết xác thực mới đã được gửi tới địa chỉ email của bạn.
                </div>
            @endif

            <div class="flex items-center justify-between mt-4">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit"
                            class="bg-pink-500 hover:bg-pink-600 text-white font-semibold
                                   px-5 py-2.5 rounded-xl transition-colors text-sm">
                        Gửi lại Email xác thực
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-sm text-gray-600 hover:text-pink-600 underline focus:outline-none">
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
