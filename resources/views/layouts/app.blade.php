<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php
        $storeSettings = \App\Models\StoreSetting::getAll();
        $storeName = $storeSettings['store_name'] ?? config('app.name', 'OnClick2Buy');
        $storeTagline = $storeSettings['store_tagline'] ?? '';
        $announcementEnabled = ($storeSettings['announcement_enabled'] ?? '0') === '1';
        $announcementText = $storeSettings['announcement_text'] ?? '';
        $socialFacebook = $storeSettings['social_facebook'] ?? '#';
        $socialTwitter = $storeSettings['social_twitter'] ?? '#';
        $socialInstagram = $storeSettings['social_instagram'] ?? '#';
        $socialTiktok = $storeSettings['social_tiktok'] ?? '#';
        $footerText = $storeSettings['footer_text'] ?? 'Made with care.';
        $metaDesc = $storeSettings['meta_description'] ?? '';
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $metaDesc }}">
    <title>@yield('title', $storeName)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50">
    <nav class="nav-blur shadow-lg sticky top-0 z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-1.5">
                        <svg class="w-6 h-6 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                        <span class="text-xl font-bold text-gray-900 tracking-tight">{{ $storeName }}</span>
                    </a>
                    <div class="ml-6 hidden md:flex space-x-1">
                        <a href="{{ route('products.index') }}" class="px-4 py-2 text-gray-600 hover:text-indigo-600 font-medium rounded-lg hover:bg-indigo-50 transition-all duration-300">Products</a>
                    </div>
                </div>
                <div class="md:hidden flex items-center">
                    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="p-2 text-gray-600 hover:text-indigo-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('cart.index') }}" class="relative p-2 text-gray-600 hover:text-indigo-600 transition-colors duration-300 group">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        @php $cartCount = \App\Models\Cart::where('session_id', session()->getId())->orWhere('user_id', auth()->id())->count(); @endphp
                        @if($cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold rounded-full h-5 min-w-[20px] flex items-center justify-center px-1 shadow-lg animate-fade-in">{{ $cartCount }}</span>
                        @endif
                    </a>
                    @auth
                        <div class="hidden md:flex items-center space-x-3">
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Admin</a>
                            @endif
                            <a href="{{ route('orders.index') }}" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">My Orders</a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="btn-outline text-sm py-1.5 px-4">Logout</button>
                            </form>
                        </div>
                        <div class="md:hidden flex items-center space-x-2">
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-indigo-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-gray-500 hover:text-red-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">Login</a>
                        <a href="{{ route('register') }}" class="btn-primary text-sm py-2 px-5">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    @if($announcementEnabled && $announcementText)
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center py-2.5 px-4 text-sm font-medium">
            {{ $announcementText }}
        </div>
    @endif

    <div id="mobile-menu" class="hidden md:hidden bg-white border-b border-gray-200 shadow-md">
        <div class="px-4 py-3 space-y-2">
            <a href="{{ route('products.index') }}" class="block px-4 py-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition-colors">Products</a>
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition-colors">Admin Panel</a>
                @endif
                <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition-colors">My Orders</a>
                <form method="POST" action="{{ route('logout') }}" class="px-4 py-2">
                    @csrf
                    <button type="submit" class="text-gray-600 hover:text-red-500 font-medium transition-colors w-full text-left">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-4 py-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition-colors">Login</a>
                <a href="{{ route('register') }}" class="block px-4 py-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg font-medium transition-colors">Register</a>
            @endauth
        </div>
    </div>

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="flash-message bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl mb-6 flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="flash-message bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-6 flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="footer-gradient text-gray-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <svg class="w-5 h-5 text-indigo-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                        <h3 class="text-lg font-bold text-white tracking-tight">{{ $storeName }}</h3>
                    </div>
                    <p class="text-sm leading-relaxed">{{ $storeTagline }}</p>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-3">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('products.index') }}" class="hover:text-indigo-400 transition-colors">All Products</a></li>
                        <li><a href="{{ route('cart.index') }}" class="hover:text-indigo-400 transition-colors">Shopping Cart</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-3">Support</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Shipping Info</a></li>
                        <li><a href="#" class="hover:text-indigo-400 transition-colors">Returns</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-3">Follow Us</h4>
                    <div class="flex space-x-3">
                        <a href="{{ $socialTwitter }}" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-indigo-600 transition-all duration-300"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg></a>
                        <a href="{{ $socialInstagram }}" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-indigo-600 transition-all duration-300"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
                        <a href="{{ $socialFacebook }}" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-indigo-600 transition-all duration-300"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-white/10 mt-8 pt-8 text-center text-sm">
                &copy; {{ date('Y') }} {{ $storeName }}. All rights reserved. {{ $footerText }}
            </div>
        </div>
    </footer>
</body>
</html>
