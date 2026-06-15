@extends('layouts.app')

@section('title', 'Quên mật khẩu')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <span class="text-4xl">🔑</span>
            <h1 class="text-2xl font-bold text-gray-800 mt-3">Quên mật khẩu</h1>
            <p class="text-gray-500 text-sm mt-2">
                Đừng lo lắng. Vui lòng nhập địa chỉ email của bạn và chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu.
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-pink-100 p-8">

            {{-- Session Status --}}
            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-6 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-pink-300
                                      @error('email') border-red-300 @enderror">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button type="submit"
                        class="w-full mt-6 bg-pink-500 hover:bg-pink-600 text-white font-semibold
                               py-3 rounded-full transition-colors">
                    Gửi liên kết đặt lại mật khẩu
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Nhớ mật khẩu rồi?
                <a href="{{ route('login') }}" class="text-pink-500 hover:text-pink-600 font-medium">
                    Đăng nhập ngay
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
