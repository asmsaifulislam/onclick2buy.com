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
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block mb-2 font-medium text-gray-700">Phone Number <span class="text-red-500">*</span></label>
                            <input type="tel" name="shipping_phone" value="{{ old('shipping_phone') }}" class="input-field" required placeholder="01XXXXXXXXX">
                            @error('shipping_phone')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block mb-2 font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="shipping_email" value="{{ old('shipping_email', Auth::user()?->email) }}" class="input-field" required placeholder="your@email.com">
                            @error('shipping_email')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block mb-2 font-medium text-gray-700">Payment Method</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($paymentMethods as $method)
                                <label class="border border-gray-200 rounded-xl p-4 cursor-pointer hover:border-indigo-400 transition-all has-checked:border-indigo-600 has-checked:bg-indigo-50 has-checked:ring-2 has-checked:ring-indigo-200">
                                    <input type="radio" name="payment_method" value="{{ $method->code }}" class="hidden" {{ old('payment_method') == $method->code ? 'checked' : '' }} required>
                                    <div class="text-center">
                                        <span class="block font-bold text-lg mb-1">
                                            @switch($method->code)
                                                @case('bkash') &#x09AC;&#x0995;&#x09BE;&#x09B6; @break
                                                @case('nagad') &#x09A8;&#x0997;&#x09A6; @break
                                                @case('rocket') &#x09B0;&#x0995;&#x09C7;&#x099F; @break
                                                @case('cod') &#x09A1;&#x09C7;&#x09B2;&#x09BF;&#x09AD;&#x09BE;&#x09B0;&#x09BF;&#x09A4;&#x09C7; @break
                                                @default {{ $method->name }}
                                            @endswitch
                                        </span>
                                        <span class="block font-medium text-sm text-gray-600">{{ $method->name }}</span>
                                        @if($method->description)
                                            <span class="block text-xs text-gray-400 mt-1">{{ $method->description }}</span>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
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
                            <span class="font-semibold text-gray-800">৳{{ number_format($price * $item->quantity, 2) }}</span>
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
