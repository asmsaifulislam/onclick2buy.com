@extends('layouts.app')
@section('title', 'Shopping Cart')
@section('content')
    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-8 animate-fade-in-up">Shopping Cart</h1>
    @if($cartItems->isEmpty())
        <div class="text-center py-16 animate-fade-in">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
            <p class="text-xl text-gray-500 mb-4">Your cart is empty</p>
            <a href="{{ route('products.index') }}" class="btn-primary inline-flex items-center gap-2">Start Shopping</a>
        </div>
    @else
        @php $total = 0; @endphp
        <div class="bg-white rounded-2xl shadow-md overflow-hidden animate-fade-in-up">
            @foreach($cartItems as $item)
                @php $price = $item->product->sale_price ?: $item->product->price; $subtotal = $price * $item->quantity; $total += $subtotal; @endphp
                <div class="cart-item flex flex-col sm:flex-row sm:items-center p-4 sm:p-5 border-b border-gray-100 gap-3 sm:gap-4">
                    <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100">
                            @if($item->product->images && count($item->product->images) > 0)
                                <img src="{{ $item->product->images[0] }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-gray-800 truncate">{{ $item->product->name }}</h3>
                            <p class="text-sm text-gray-500">${{ number_format($price, 2) }} each</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 sm:gap-2 flex-wrap sm:flex-nowrap">
                        <form action="{{ route('cart.update', $item) }}" method="POST" class="flex items-center gap-2">
                            @csrf @method('PATCH')
                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="w-16 input-field text-center py-1.5 text-sm">
                            <button type="submit" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors">Update</button>
                        </form>
                        <p class="font-bold text-gray-800 whitespace-nowrap">${{ number_format($subtotal, 2) }}</p>
                        <form action="{{ route('cart.remove', $item) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 transition-colors p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
            <div class="p-6 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Total Items: <span class="font-semibold">{{ $cartItems->sum('quantity') }}</span></p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-extrabold text-gray-900">${{ number_format($total, 2) }}</p>
                    <a href="{{ route('checkout.index') }}" class="btn-primary mt-2 inline-flex items-center gap-2">
                        Proceed to Checkout
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="mt-6 text-center">
            <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium inline-flex items-center gap-1 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Continue Shopping
            </a>
        </div>
    @endif
@endsection
