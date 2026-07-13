@extends('layouts.app')
@section('title', isset($category) ? $category->name : 'Products')
@section('content')
    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-2 animate-fade-in-up">{{ isset($category) ? $category->name : 'All Products' }}</h1>
    @if(isset($category))
        <p class="text-gray-500 mb-8 animate-fade-in-up animate-delay-1">{{ $category->description }}</p>
    @endif

    @if(isset($categories))
        <div class="flex flex-wrap gap-2 mb-8 animate-fade-in-up">
            <a href="{{ route('products.index') }}" class="px-5 py-2.5 rounded-xl font-medium transition-all duration-300 {{ !isset($category) ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100 shadow-sm border border-gray-200' }}">All</a>
            @foreach($categories as $cat)
                <a href="{{ route('products.category', $cat) }}" class="px-5 py-2.5 rounded-xl font-medium transition-all duration-300 {{ isset($category) && $category->id === $cat->id ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100 shadow-sm border border-gray-200' }}">{{ $cat->name }}</a>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($products as $i => $product)
            <div class="product-card bg-white rounded-xl shadow-md overflow-hidden group animate-fade-in-up" style="animation-delay: {{ $i * 0.05 }}s">
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
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center"><span class="bg-white text-gray-800 px-4 py-2 rounded-lg font-bold text-sm">Out of Stock</span></div>
                    @endif
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">{{ $product->category->name ?? 'Uncategorized' }}</p>
                    <a href="{{ route('products.show', $product) }}" class="font-semibold text-gray-800 group-hover:text-indigo-600 transition-colors line-clamp-1">{{ $product->name }}</a>
                    <div class="mt-2 flex items-center gap-2">
                        @if($product->sale_price)
                            <span class="text-xl font-bold text-red-600">৳{{ number_format($product->sale_price, 2) }}</span>
                            <span class="text-sm text-gray-400 line-through">৳{{ number_format($product->price, 2) }}</span>
                        @else
                            <span class="text-xl font-bold text-gray-900">৳{{ number_format($product->price, 2) }}</span>
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
            <p class="col-span-full text-center text-gray-400 py-16 text-lg">No products found.</p>
        @endforelse
    </div>

    <div class="mt-10 pagination-custom">
        {{ $products->links() }}
    </div>
@endsection
