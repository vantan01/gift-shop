@extends('layouts.app')

@section('title', 'Tài khoản của tôi')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">

    <h1 class="text-2xl font-bold text-gray-800 mb-8">Tài khoản của tôi</h1>

    {{-- Success message --}}
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 mb-6 text-sm">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- Profile Form --}}
    <div class="bg-white rounded-2xl border border-pink-100 p-6 mb-6">
        <h2 class="font-semibold text-gray-700 mb-5">Thông tin cá nhân</h2>

        <form method="POST" action="{{ route('account.update') }}">
            @csrf
            @method('PATCH')

            <div class="space-y-4">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Họ và tên
                    </label>
                    <input type="text"
                           name="name"
                           value="{{ old('name', $user->name) }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300
                                  @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email (readonly) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Email
                    </label>
                    <input type="email"
                           value="{{ $user->email }}"
                           disabled
                           class="w-full border border-gray-100 rounded-xl px-4 py-2.5 text-sm
                                  bg-gray-50 text-gray-400 cursor-not-allowed">
                    <p class="text-xs text-gray-400 mt-1">Email không thể thay đổi.</p>
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Số điện thoại
                    </label>
                    <input type="text"
                           name="phone"
                           value="{{ old('phone', $user->phone) }}"
                           placeholder="0909 xxx xxx"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300
                                  @error('phone') border-red-300 @enderror">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Address --}}
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        Địa chỉ giao hàng
                    </label>
                    <input type="text"
                           name="address"
                           value="{{ old('address', $user->address) }}"
                           placeholder="Số nhà, đường, phường, quận, tỉnh/thành"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-pink-300
                                  @error('address') border-red-300 @enderror">
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <span class="text-xs text-gray-400">
                    Role: {{ $user->role->label() }}
                </span>
                <button type="submit"
                        class="bg-pink-500 hover:bg-pink-600 text-white text-sm font-semibold
                               px-6 py-2.5 rounded-full transition-colors">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>

    {{-- Logout --}}
    <div class="bg-white rounded-2xl border border-pink-100 p-6">
        <h2 class="font-semibold text-gray-700 mb-3">Đăng xuất</h2>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="text-sm text-red-500 hover:text-red-600 font-medium transition-colors">
                Đăng xuất khỏi tài khoản
            </button>
        </form>
    </div>

</div>
@endsection