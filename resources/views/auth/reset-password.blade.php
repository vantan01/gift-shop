@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <span class="text-4xl">🔐</span>
            <h1 class="text-2xl font-bold text-gray-800 mt-3">Đặt lại mật khẩu</h1>
            <p class="text-gray-500 text-sm mt-1">Vui lòng nhập mật khẩu mới của bạn</p>
        </div>

        <div class="bg-white rounded-2xl border border-pink-100 p-8">

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="space-y-4">
                    <!-- Email Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-pink-300
                                      @error('email') border-red-300 @enderror">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Mật khẩu mới</label>
                        <input type="password" name="password" required autocomplete="new-password"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-pink-300
                                      @error('password') border-red-300 @enderror">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-pink-300
                                      @error('password_confirmation') border-red-300 @enderror">
                        @error('password_confirmation')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button type="submit"
                        class="w-full mt-6 bg-pink-500 hover:bg-pink-600 text-white font-semibold
                               py-3 rounded-full transition-colors">
                    Lưu mật khẩu mới
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
