<header class="bg-white border-b border-pink-100 sticky top-0 z-50" x-data="{ mobileOpen: false }">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 group">
            <span class="text-2xl">🎁</span>
            <span class="text-xl font-bold text-pink-500 group-hover:text-pink-600 transition-colors">
                Gift Shop
            </span>
        </a>

        {{-- Desktop Navigation --}}
        <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
            <a href="{{ route('home') }}"
               class="{{ request()->routeIs('home') ? 'text-pink-500' : 'text-gray-600 hover:text-pink-500' }} transition-colors">
                Trang chủ
            </a>
            <a href="{{ route('products.index') }}"
               class="{{ request()->routeIs('products.*') ? 'text-pink-500' : 'text-gray-600 hover:text-pink-500' }} transition-colors">
                Sản phẩm
            </a>

            @if (isset($navCategories) && $navCategories->isNotEmpty())
            <div class="relative group">
                <button type="button"
                        class="flex items-center gap-1 text-gray-600 hover:text-pink-500 transition-colors
                               {{ request()->routeIs('categories.*') ? 'text-pink-500' : '' }}">
                    Danh mục
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="absolute left-0 top-full mt-2 w-52 bg-white border border-pink-100
                            rounded-xl shadow-lg py-2 opacity-0 invisible
                            group-hover:opacity-100 group-hover:visible transition-all z-50">
                    @foreach ($navCategories as $cat)
                        <a href="{{ route('categories.show', $cat) }}"
                           class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600
                                  hover:text-pink-500 hover:bg-pink-50 transition-colors">
                            <span>{{ $cat->emoji() }}</span>
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </nav>

        {{-- Right Actions --}}
        <div class="flex items-center gap-2 md:gap-3">
            {{-- Cart --}}
            <a href="{{ route('cart.index') }}"
               class="relative p-2 text-gray-600 hover:text-pink-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 7H4l1-7z" />
                </svg>
                @if (($cartCount ?? 0) > 0)
                <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-pink-500
                             text-white text-[10px] font-bold rounded-full flex items-center
                             justify-center px-1">
                    {{ $cartCount > 99 ? '99+' : $cartCount }}
                </span>
                @endif
            </a>

            @auth
            <div class="relative group hidden md:block">
                <button type="button"
                        class="flex items-center gap-2 text-sm text-gray-600 hover:text-pink-500 transition-colors">
                    <span class="w-8 h-8 rounded-full bg-pink-100 text-pink-600
                                 flex items-center justify-center font-semibold text-xs">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <span>{{ Auth::user()->name }}</span>
                </button>
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
                                class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}"
               class="hidden md:inline text-sm text-gray-600 hover:text-pink-500 transition-colors">
                Đăng nhập
            </a>
            <a href="{{ route('register') }}"
               class="hidden md:inline bg-pink-500 hover:bg-pink-600 text-white text-sm px-4 py-2
                      rounded-full transition-colors">
                Đăng ký
            </a>
            @endauth

            {{-- Mobile menu toggle --}}
            <button type="button"
                    @click="mobileOpen = !mobileOpen"
                    class="md:hidden p-2 text-gray-600 hover:text-pink-500 transition-colors"
                    aria-label="Mở menu">
                <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Navigation --}}
    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="md:hidden border-t border-pink-100 bg-white">
        <nav class="max-w-6xl mx-auto px-4 py-4 space-y-1">
            <a href="{{ route('home') }}"
               class="block px-3 py-2 rounded-lg text-sm font-medium
                      {{ request()->routeIs('home') ? 'bg-pink-50 text-pink-600' : 'text-gray-600' }}">
                Trang chủ
            </a>
            <a href="{{ route('products.index') }}"
               class="block px-3 py-2 rounded-lg text-sm font-medium
                      {{ request()->routeIs('products.*') ? 'bg-pink-50 text-pink-600' : 'text-gray-600' }}">
                Sản phẩm
            </a>

            @if (isset($navCategories) && $navCategories->isNotEmpty())
            <p class="px-3 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wide">Danh mục</p>
            @foreach ($navCategories as $cat)
                <a href="{{ route('categories.show', $cat) }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm
                          {{ request()->routeIs('categories.show') && request()->route('category')?->id === $cat->id
                              ? 'bg-pink-50 text-pink-600 font-medium'
                              : 'text-gray-600' }}">
                    <span>{{ $cat->emoji() }}</span>
                    {{ $cat->name }}
                </a>
            @endforeach
            @endif

            <hr class="my-2 border-pink-100">

            @auth
                <a href="{{ route('account') }}" class="block px-3 py-2 rounded-lg text-sm text-gray-600">Tài khoản</a>
                <a href="{{ route('orders.index') }}" class="block px-3 py-2 rounded-lg text-sm text-gray-600">Đơn hàng</a>
                @if (Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm text-gray-600">Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm text-red-500">
                        Đăng xuất
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-lg text-sm text-gray-600">Đăng nhập</a>
                <a href="{{ route('register') }}"
                   class="block px-3 py-2 rounded-lg text-sm font-medium text-pink-600 bg-pink-50 text-center">
                    Đăng ký
                </a>
            @endauth
        </nav>
    </div>
</header>
