@extends('layouts.app')
@section('title', 'Checkout')
@section('content')
    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-8 animate-fade-in-up">Checkout</h1>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 animate-fade-in-up">
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl shadow-md p-6 md:p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Shipping Details</h2>
                <form action="{{ route('checkout.store') }}" method="POST">
                    @csrf
                    <div class="mb-5">
                        <label class="block mb-2 font-medium text-gray-700">Shipping Address</label>
                        <textarea name="shipping_address" rows="3" class="input-field" required placeholder="Street, city, zip code, country...">{{ old('shipping_address') }}</textarea>
                        @error('shipping_address')<p class="text-red-500 text-sm mt-1 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> {{ $message }}</p>@enderror
                    </div>
                    <div class="mb-6">
                        <label class="block mb-2 font-medium text-gray-700">Payment Method</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-indigo-400 transition-all has-checked:border-indigo-600 has-checked:bg-indigo-50 has-checked:ring-2 has-checked:ring-indigo-200">
                                <input type="radio" name="payment_method" value="cash_on_delivery" class="hidden" {{ old('payment_method') == 'cash_on_delivery' ? 'checked' : '' }} required>
                                <div class="text-center">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span class="block font-medium text-sm">Cash on Delivery</span>
                                </div>
                            </label>
                            <label class="border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-indigo-400 transition-all has-checked:border-indigo-600 has-checked:bg-indigo-50 has-checked:ring-2 has-checked:ring-indigo-200">
                                <input type="radio" name="payment_method" value="bank_transfer" class="hidden" {{ old('payment_method') == 'bank_transfer' ? 'checked' : '' }}>
                                <div class="text-center">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span class="block font-medium text-sm">Bank Transfer</span>
                                </div>
                            </label>
                        </div>
                        @error('payment_method')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn-primary w-full py-3.5 text-lg flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Place Order
                    </button>
                </form>
            </div>
        </div>
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-md p-6 md:p-8 sticky top-24">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>
                <div class="space-y-4">
                    @foreach($cartItems as $item)
                        @php $price = $item->product->sale_price ?: $item->product->price; @endphp
                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                                    @if($item->product->images && count($item->product->images) > 0)
                                        <img src="{{ $item->product->images[0] }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-sm text-gray-800">{{ $item->product->name }}</p>
                                    <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                                </div>
                            </div>
                            <span class="font-semibold text-gray-800">${{ number_format($price * $item->quantity, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-gray-200 pt-4 mt-4 space-y-2">
                    <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>${{ number_format($total, 2) }}</span></div>
                    <div class="flex justify-between text-gray-600"><span>Shipping</span><span class="text-green-600 font-medium">Free</span></div>
                    <div class="flex justify-between text-xl font-extrabold text-gray-900 pt-2 border-t"><span>Total</span><span>${{ number_format($total, 2) }}</span></div>
                </div>
            </div>
        </div>
    </div>
@endsection
