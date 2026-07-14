@extends('layouts.app')
@section('title', 'My Wishlist')
@section('content')
    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-8 animate-fade-in-up">My Wishlist</h1>
    @if($items->isEmpty())
        <div class="text-center py-16 animate-fade-in bg-white rounded-2xl shadow-sm">
            <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <p class="text-xl text-gray-500 mb-4">Your wishlist is empty</p>
            <a href="{{ route('products.index') }}" class="btn-primary inline-flex items-center gap-2">Browse Products</a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($items as $item)
                @php $product = $item->product; @endphp
                @if($product)
                    <div class="product-card bg-white rounded-xl shadow-md overflow-hidden group animate-fade-in-up">
                        <a href="{{ route('products.show', $product) }}">
                            @if($product->images && count($product->images) > 0)
                                <img src="{{ $product->images[0] }}" alt="{{ $product->name }}" class="product-img h-44 w-full object-cover">
                            @else
                                <div class="h-44 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </a>
                        <div class="p-4">
                            <a href="{{ route('products.show', $product) }}" class="font-semibold text-gray-800 group-hover:text-indigo-600 transition-colors line-clamp-2">{{ $product->name }}</a>
                            <p class="text-lg font-bold mt-1">৳{{ number_format($product->sale_price ?: $product->price, 2) }}</p>
                            <div class="flex items-center gap-2 mt-3">
                                <a href="{{ route('products.show', $product) }}" class="flex-1 text-center btn-primary text-sm py-2">View</a>
                                <form action="{{ route('wishlist.destroy', $product) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Remove">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        <div class="mt-8">
            {{ $items->links() }}
        </div>
    @endif
@endsection
