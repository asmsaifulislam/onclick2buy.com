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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $storeName)</title>
    @yield('meta')
    @if(config('app.google_analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('app.google_analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config('app.google_analytics_id') }}');
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (navigator.sendBeacon) {
                    navigator.sendBeacon('/api/track/page-view', JSON.stringify({
                        url: window.location.href,
                        path: window.location.pathname,
                        referrer: document.referrer || '',
                    }));
                }
            });
        </script>
    @endif
    @if(config('mautic.tracking.enabled') && config('mautic.tracking.pixel_id'))
        <script>
            (function() {
                var defined = null;
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.async = true;
                script.src = '{{ config('mautic.base_url') }}/mtc/pixel.js?id={{ config('mautic.tracking.pixel_id') }}';
                document.getElementsByTagName('head')[0].appendChild(script);
            })();
        </script>
    @endif
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
                        <a href="{{ route('wishlist.index') }}" class="relative p-2 text-gray-600 hover:text-red-500 transition-colors duration-300 group">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            @php $wishCount = \App\Models\Wishlist::where('user_id', auth()->id())->count(); @endphp
                            @if($wishCount > 0)
                                <span class="absolute -top-1 -right-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold rounded-full h-5 min-w-[20px] flex items-center justify-center px-1 shadow-lg animate-fade-in">{{ $wishCount }}</span>
                            @endif
                        </a>
                    @endauth
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

    {{-- Live Chat Widget --}}
    <div id="chat-widget" class="fixed bottom-6 right-6 z-50 font-sans">
        {{-- Chat Toggle Button --}}
        <button id="chat-toggle" onclick="toggleChat()" class="w-14 h-14 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-full shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-110">
            <svg id="chat-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <svg id="chat-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span id="chat-unread" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 hidden items-center justify-center">0</span>
        </button>

        {{-- Chat Window --}}
        <div id="chat-window" class="hidden absolute bottom-20 right-0 w-[380px] max-w-[calc(100vw-3rem)] bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden" style="height: 500px;">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm">Need Help?</h3>
                        <p class="text-xs text-white/80">We typically reply within minutes</p>
                    </div>
                </div>
            </div>

            {{-- Messages --}}
            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50" style="height: 370px;">
                <div class="text-center">
                    <span class="text-xs text-gray-400 bg-white px-3 py-1 rounded-full">Today</span>
                </div>
            </div>

            {{-- Input --}}
            <div class="border-t border-gray-200 p-3 bg-white">
                <form id="chat-send-form" class="flex items-center gap-2">
                    <input type="text" id="chat-message-input" placeholder="Type a message..." class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" autocomplete="off">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl w-10 h-10 flex items-center justify-center transition-colors flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    (function() {
        let chatOpen = false;
        let sessionId = null;
        let visitorId = null;
        let lastMessageId = 0;
        let chatInitialized = false;

        window.toggleChat = function() {
            chatOpen = !chatOpen;
            document.getElementById('chat-window').classList.toggle('hidden', !chatOpen);
            document.getElementById('chat-icon-open').classList.toggle('hidden', chatOpen);
            document.getElementById('chat-icon-close').classList.toggle('hidden', !chatOpen);
            if (chatOpen && !chatInitialized) initChat();
        };

        function addChatMessage(msg) {
            const container = document.getElementById('chat-messages');
            const isVisitor = !msg.is_admin;
            const wrapper = document.createElement('div');
            wrapper.className = 'flex ' + (isVisitor ? 'justify-end' : 'justify-start');
            const bubble = isVisitor
                ? 'bg-indigo-600 text-white rounded-br-md'
                : 'bg-white text-gray-800 border border-gray-200 rounded-bl-md shadow-sm';
            wrapper.innerHTML = `
                <div class="max-w-[85%]">
                    <div class="px-3 py-2 rounded-2xl ${bubble}">
                        <p class="text-sm">${escapeHtml(msg.message)}</p>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-0.5 ${isVisitor ? 'text-right' : ''}">${escapeHtml(msg.sender)} &middot; ${msg.created_at}</p>
                </div>`;
            container.appendChild(wrapper);
            container.scrollTop = container.scrollHeight;
        }

        async function initChat() {
            chatInitialized = true;
            try {
                const res = await fetch('/chat/session', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({}),
                });
                const data = await res.json();
                sessionId = data.session_id;
                visitorId = data.visitor_id;
                lastMessageId = data.messages.length > 0 ? data.messages[data.messages.length - 1].id : 0;
                data.messages.forEach(m => addChatMessage(m));
                if (data.messages.length === 0) {
                    addChatMessage({ message: 'Hello! How can we help you today?', is_admin: true, sender: 'Support', created_at: new Date().toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit'}) });
                }
            } catch(e) { console.error(e); }
        }

        document.getElementById('chat-send-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const input = document.getElementById('chat-message-input');
            const text = input.value.trim();
            if (!text) return;
            input.value = '';
            if (!chatInitialized) await initChat();
            try {
                const res = await fetch('/chat/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: text, visitor_id: visitorId, session_id: sessionId }),
                });
                const data = await res.json();
                if (data.id) {
                    lastMessageId = data.id;
                    addChatMessage(data);
                }
            } catch(e) { console.error(e); }
        });

        setInterval(async () => {
            if (!chatOpen || !sessionId) return;
            try {
                const res = await fetch('/chat/poll?session_id=' + sessionId + '&last_id=' + lastMessageId, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                const data = await res.json();
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(m => { if (m.id > lastMessageId) { lastMessageId = m.id; addChatMessage(m); } });
                }
            } catch(e) {}
        }, 5000);

        function escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        }
    })();
    </script>

    {{-- AI Chatbot Widget --}}
    <div id="ai-bot-widget" class="fixed bottom-6 left-6 z-50">
        <button id="ai-bot-toggle" onclick="toggleAiBot()" class="w-14 h-14 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white rounded-full shadow-xl flex items-center justify-center transition-all duration-300 hover:scale-110">
            <svg id="ai-bot-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            <svg id="ai-bot-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div id="ai-bot-window" class="hidden absolute bottom-20 left-0 w-[380px] max-w-[calc(100vw-3rem)] bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden" style="height: 500px;">
            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white p-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm">AI Assistant</h3>
                        <p class="text-xs text-white/80">Powered by AI - Ask me anything!</p>
                    </div>
                </div>
            </div>

            <div id="ai-bot-messages" class="overflow-y-auto p-4 space-y-3 bg-gray-50" style="height: 370px;">
                <div class="flex justify-start">
                    <div class="max-w-[85%]">
                        <div class="px-3 py-2 rounded-2xl bg-white text-gray-800 border border-gray-200 rounded-bl-md shadow-sm">
                            <p class="text-sm">Hi! I'm your AI assistant. I can help you find products, track orders, answer questions about our store, and more. What can I help you with?</p>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-0.5">AI Bot</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 p-3 bg-white">
                <div class="flex gap-2 mb-2">
                    <button onclick="sendAiQuick('Find me a product')" class="text-[10px] px-2 py-1 bg-yellow-50 text-yellow-700 rounded-full hover:bg-yellow-100 transition-colors">Find Product</button>
                    <button onclick="sendAiQuick('Track my order')" class="text-[10px] px-2 py-1 bg-yellow-50 text-yellow-700 rounded-full hover:bg-yellow-100 transition-colors">Track Order</button>
                    <button onclick="sendAiQuick('What are your deals?')" class="text-[10px] px-2 py-1 bg-yellow-50 text-yellow-700 rounded-full hover:bg-yellow-100 transition-colors">Deals</button>
                </div>
                <form id="ai-bot-form" class="flex items-center gap-2">
                    <input type="text" id="ai-bot-input" placeholder="Ask AI anything..." class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" autocomplete="off">
                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl w-10 h-10 flex items-center justify-center transition-colors flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    (function() {
        let aiBotOpen = false;
        let aiSessionId = null;

        window.toggleAiBot = function() {
            aiBotOpen = !aiBotOpen;
            document.getElementById('ai-bot-window').classList.toggle('hidden', !aiBotOpen);
            document.getElementById('ai-bot-icon-open').classList.toggle('hidden', aiBotOpen);
            document.getElementById('ai-bot-icon-close').classList.toggle('hidden', !aiBotOpen);
        };

        window.sendAiQuick = function(text) {
            document.getElementById('ai-bot-input').value = text;
            document.getElementById('ai-bot-form').dispatchEvent(new Event('submit'));
        };

        function addAiBotMessage(text, isBot) {
            const container = document.getElementById('ai-bot-messages');
            const wrapper = document.createElement('div');
            wrapper.className = 'flex ' + (isBot ? 'justify-start' : 'justify-end');
            const bubble = isBot
                ? 'bg-white text-gray-800 border border-gray-200 rounded-bl-md shadow-sm'
                : 'bg-yellow-500 text-white rounded-br-md';
            wrapper.innerHTML = `
                <div class="max-w-[85%]">
                    <div class="px-3 py-2 rounded-2xl ${bubble}">
                        <p class="text-sm">${escapeHtml(text)}</p>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-0.5 ${isBot ? '' : 'text-right'}">${isBot ? 'AI Assistant' : 'You'} &middot; ${new Date().toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit'})}</p>
                </div>`;
            container.appendChild(wrapper);
            container.scrollTop = container.scrollHeight;
        }

        function showTyping() {
            const container = document.getElementById('ai-bot-messages');
            const typing = document.createElement('div');
            typing.id = 'ai-typing';
            typing.className = 'flex justify-start';
            typing.innerHTML = '<div class="max-w-[85%]"><div class="px-3 py-2 rounded-2xl bg-white border border-gray-200 rounded-bl-md shadow-sm"><div class="flex gap-1"><span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span><span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span><span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span></div></div></div>';
            container.appendChild(typing);
            container.scrollTop = container.scrollHeight;
        }

        function removeTyping() {
            const el = document.getElementById('ai-typing');
            if (el) el.remove();
        }

        document.getElementById('ai-bot-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const input = document.getElementById('ai-bot-input');
            const text = input.value.trim();
            if (!text) return;
            input.value = '';
            addAiBotMessage(text, false);
            showTyping();

            try {
                const res = await fetch('/api/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: text, session_id: aiSessionId }),
                });
                const data = await res.json();
                removeTyping();
                if (data.session_id) aiSessionId = data.session_id;
                addAiBotMessage(data.reply || data.message || 'Sorry, I could not understand that.', true);
            } catch(err) {
                removeTyping();
                addAiBotMessage('I am having trouble connecting. Please try again or contact our support team.', true);
            }
        });

        function escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        }
    })();
    </script>
</body>
</html>
