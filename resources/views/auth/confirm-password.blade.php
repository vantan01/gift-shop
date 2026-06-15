@extends('layouts.app')

@section('title', 'Xác nhận mật khẩu')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <span class="text-4xl">🔒</span>
            <h1 class="text-2xl font-bold text-gray-800 mt-3">Khu vực bảo mật</h1>
            <p class="text-gray-500 text-sm mt-2">
                Đây là khu vực bảo mật của ứng dụng. Vui lòng xác nhận mật khẩu của bạn trước khi tiếp tục.
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-pink-100 p-8">

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="space-y-4">
                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Mật khẩu</label>
                        <input type="password" name="password" required autocomplete="current-password"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-pink-300
                                      @error('password') border-red-300 @enderror">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit"
                            class="bg-pink-500 hover:bg-pink-600 text-white font-semibold
                                   px-6 py-2.5 rounded-full transition-colors">
                        Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
