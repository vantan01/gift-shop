<header class="bg-white border-b border-pink-100 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">

        {{-- Logo --}}
        <a href="/" class="flex items-center gap-2 group">
            <span class="text-2xl">🎁</span>
            <span class="text-xl font-bold text-pink-500 group-hover:text-pink-600 transition-colors">
                Gift Shop
            </span>
        </a>

        {{-- Navigation --}}
        <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
            <a href="/" class="text-gray-600 hover:text-pink-500 transition-colors">
                Trang chủ
            </a>
            <a href="/products" class="text-gray-600 hover:text-pink-500 transition-colors">
                Sản phẩm
            </a>
        </nav>

        {{-- Right Actions --}}
        <div class="flex items-center gap-3">
            {{-- Cart Icon --}}
            <a href="{{ route('cart.index') }}"
                class="relative p-2 text-gray-600 hover:text-pink-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 7H4l1-7z" />
                </svg>
                @php $cartCount = app(\App\Services\CartService::class)->getCount(); @endphp
                @if ($cartCount > 0)
                <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-pink-500
                     text-white text-[10px] font-bold rounded-full flex items-center
                     justify-center px-1">
                    {{ $cartCount > 99 ? '99+' : $cartCount }}
                </span>
                @endif
            </a>

            @auth
            {{-- Dropdown user --}}
            <div class="relative group">
                <button class="flex items-center gap-2 text-sm text-gray-600
                           hover:text-pink-500 transition-colors">
                    <span class="w-8 h-8 rounded-full bg-pink-100 text-pink-600
                             flex items-center justify-center font-semibold text-xs">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <span class="hidden md:inline">{{ Auth::user()->name }}</span>
                </button>

                {{-- Dropdown --}}
                <div class="absolute right-0 top-full mt-2 w-48 bg-white border border-pink-100
                        rounded-xl shadow-lg py-1 opacity-0 invisible
                        group-hover:opacity-100 group-hover:visible transition-all z-50">
                    <a href="{{ route('account') }}"
                        class="block px-4 py-2 text-sm text-gray-600 hover:text-pink-500 hover:bg-pink-50">
                        Tài khoản
                    </a>
                    <a href="{{ route('orders.index') }}"
                        class="block px-4 py-2 text-sm text-gray-600 hover:text-pink-500 hover:bg-pink-50">
                        Đơn hàng của tôi
                    </a>
                    @if (Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}"
                        class="block px-4 py-2 text-sm text-gray-600 hover:text-pink-500 hover:bg-pink-50">
                        ⚙️ Admin
                    </a>
                    @endif
                    <hr class="my-1 border-pink-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2 text-sm text-red-500
                                   hover:bg-red-50 transition-colors">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}"
                class="text-sm text-gray-600 hover:text-pink-500 transition-colors">
                Đăng nhập
            </a>
            <a href="{{ route('register') }}"
                class="bg-pink-500 hover:bg-pink-600 text-white text-sm px-4 py-2
                  rounded-full transition-colors">
                Đăng ký
            </a>
            @endauth
        </div>
    </div>
</header>