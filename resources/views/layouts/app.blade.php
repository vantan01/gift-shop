<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        {{-- [Antigravity EDIT - Start] - Chuyển đổi layout Breeze mặc định sang layout Gift Shop có header/footer --}}
        {{-- Code cũ:
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                @yield('content')
            </main>
        </div>
        --}}
        <div class="min-h-screen bg-gray-50 flex flex-col justify-between">
            @include('layouts.header')

            <!-- Page Content -->
            <main class="flex-grow">
                @yield('content')
            </main>

            @include('layouts.footer')
        </div>
        {{-- [Antigravity EDIT - End] --}}
    </body>
</html>
