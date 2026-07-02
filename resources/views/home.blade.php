@extends('layouts.app')
@section('title', 'Home')
@section('content')
    @php
        $heroTitle = $storeSettings['hero_title'] ?? 'Premium Shopping Experience';
        $heroSubtitle = $storeSettings['hero_subtitle'] ?? 'Discover curated collections of high-quality products with unbeatable prices and fast delivery.';
        $heroCta = $storeSettings['hero_cta_text'] ?? 'Shop Now';
    @endphp
    <div class="hero-gradient text-white rounded-2xl p-6 sm:p-10 md:p-16 mb-12 text-center relative overflow-hidden shadow-2xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMiIvPjwvZz48L2c+PC9zdmc+')] opacity-50"></div>
        <div class="relative z-10">
            <h1 class="text-3xl sm:text-4xl md:text-6xl font-extrabold mb-4 tracking-tight animate-fade-in-up">{{ $heroTitle }}</h1>
            <p class="text-lg md:text-xl text-indigo-200 mb-8 max-w-2xl mx-auto animate-fade-in-up animate-delay-1">{{ $heroSubtitle }}</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 animate-fade-in-up animate-delay-2">
                <a href="{{ route('products.index') }}" class="bg-white text-indigo-700 px-8 py-3.5 rounded-xl font-bold hover:bg-indigo-50 transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1">{{ $heroCta }}</a>
                <a href="#featured" class="border-2 border-white/40 text-white px-8 py-3.5 rounded-xl font-semibold hover:bg-white/10 transition-all duration-300 hover:-translate-y-1">Explore</a>
            </div>
        </div>
    </div>

    @if($categories->isNotEmpty())
        <section class="mb-14">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Shop by Category</h2>
                <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1 transition-colors">View All <span>&rarr;</span></a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($categories as $i => $category)
                    <a href="{{ route('products.category', $category) }}" class="category-card bg-white rounded-xl shadow-md overflow-hidden group animate-fade-in-up" style="animation-delay: {{ $i * 0.1 }}s">
                        <div class="h-36 overflow-hidden">
                            @if($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-4 text-center">
                            <h3 class="font-bold text-gray-800 group-hover:text-indigo-600 transition-colors">{{ $category->name }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section id="featured">
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Featured Products</h2>
            <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1 transition-colors">View All <span>&rarr;</span></a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($products as $i => $product)
                <div class="product-card bg-white rounded-xl shadow-md overflow-hidden group animate-fade-in-up" style="animation-delay: {{ $i * 0.08 }}s">
                    <div class="relative overflow-hidden">
                        <a href="{{ route('products.show', $product) }}">
                            @if($product->images && count($product->images) > 0)
                                <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="product-img h-52 w-full object-cover">
                            @else
                                <div class="h-52 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </a>
                        @if($product->sale_price)
                            <span class="badge-sale absolute top-3 right-3 text-white text-xs font-bold px-3 py-1 rounded-full">SALE</span>
                        @endif
                        @if($product->stock <= 0)
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                <span class="bg-white text-gray-800 px-4 py-2 rounded-lg font-bold text-sm">Out of Stock</span>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">{{ $product->category->name ?? 'Uncategorized' }}</p>
                        <a href="{{ route('products.show', $product) }}" class="font-semibold text-gray-800 group-hover:text-indigo-600 transition-colors line-clamp-1">{{ $product->name }}</a>
                        <div class="mt-2 flex items-center gap-2">
                            @if($product->sale_price)
                                <span class="text-xl font-bold text-red-600">${{ number_format($product->sale_price, 2) }}</span>
                                <span class="text-sm text-gray-400 line-through">${{ number_format($product->price, 2) }}</span>
                            @else
                                <span class="text-xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                            @endif
                        </div>
                        @if($product->stock > 0)
                            <form action="{{ route('cart.add', $product) }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit" class="btn-primary w-full text-sm py-2.5 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                    Add to Cart
                                </button>
                            </form>
                        @else
                            <button disabled class="w-full mt-3 bg-gray-100 text-gray-400 py-2.5 rounded-xl font-medium cursor-not-allowed">Out of Stock</button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-gray-400 py-16 text-lg">No products available yet. Check back soon!</p>
            @endforelse
        </div>
    </section>
@endsection
