<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Gift Shop</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">

<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-60 bg-white border-r border-gray-100 flex flex-col fixed top-0 left-0 h-full z-40">

        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-gray-100">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <span class="text-xl">🎁</span>
                <div>
                    <p class="font-bold text-gray-800 text-sm leading-tight">Gift Shop</p>
                    <p class="text-xs text-gray-400">Admin Panel</p>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            @php
                $navItems = [
                    ['route' => 'admin.dashboard',        'icon' => '📊', 'label' => 'Dashboard'],
                    ['route' => 'admin.products.index',   'icon' => '🛍️', 'label' => 'Sản phẩm'],
                    ['route' => 'admin.categories.index', 'icon' => '🗂️', 'label' => 'Danh mục'],
                    ['route' => 'admin.orders.index',     'icon' => '📦', 'label' => 'Đơn hàng'],
                ];
            @endphp

            @foreach ($navItems as $item)
                @php
                    $isActive = request()->routeIs($item['route'] . '*');
                @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium
                          transition-colors
                          {{ $isActive
                              ? 'bg-pink-50 text-pink-600'
                              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span class="text-base">{{ $item['icon'] }}</span>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Bottom: back to store + logout --}}
        <div class="px-3 py-4 border-t border-gray-100 space-y-0.5">
            <a href="{{ route('home') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-gray-500
                      hover:bg-gray-50 transition-colors">
                <span>🏪</span> Về cửa hàng
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm
                               text-red-500 hover:bg-red-50 transition-colors">
                    <span>🚪</span> Đăng xuất
                </button>
            </form>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 ml-60">

        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between sticky top-0 z-30">
            <div>
                <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                @hasSection('breadcrumb')
                    <div class="text-xs text-gray-400 mt-0.5">@yield('breadcrumb')</div>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">
                    {{ Auth::user()->name }}
                </span>
                <div class="w-8 h-8 rounded-full bg-pink-100 text-pink-600
                            flex items-center justify-center font-semibold text-xs">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="p-6">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700
                            rounded-xl px-4 py-3 mb-6 text-sm flex items-center gap-2">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700
                            rounded-xl px-4 py-3 mb-6 text-sm flex items-center gap-2">
                    <span>⚠️</span> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

</body>
</html>