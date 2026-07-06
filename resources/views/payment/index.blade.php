@extends('layouts.app')
@section('title', 'Payment - Order #' . $order->order_number)
@section('content')
<div class="max-w-3xl mx-auto animate-fade-in-up">
    <div class="bg-white rounded-2xl shadow-md p-6 md:p-8">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900">Complete Payment</h1>
                <p class="text-sm text-gray-500">Order: {{ $order->order_number }} &middot; Total: <strong>${{ number_format($order->total, 2) }}</strong></p>
            </div>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
        @endif

        @if($method)
            @if($method->code === 'cod')
                {{-- COD --}}
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Cash on Delivery</h2>
                    <p class="text-gray-500 mb-6">Pay when you receive your order. No advance payment needed.</p>
                    <form method="POST" action="{{ route('payment.process', $order) }}">
                        @csrf
                        <button type="submit" class="btn-primary py-3 px-8">Confirm COD Order</button>
                    </form>
                </div>
            @else
                {{-- SSLCommerz Payment Gateway --}}
                <div class="text-center py-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-2">Pay with {{ $method->name }}</h2>
                        <p class="text-gray-500">You will be redirected to SSLCommerz secure payment gateway.</p>
                    </div>

                    @if($method->code !== 'card')
                    <div class="mb-6 p-4 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl border border-indigo-100 inline-block text-left">
                        <p class="text-sm text-gray-500 mb-1">Pay via</p>
                        <div class="flex items-center gap-4 text-lg font-bold">
                            <span class="text-indigo-700">{{ $method->account_number }}</span>
                            <span class="text-gray-400 text-base">({{ $method->account_name }})</span>
                        </div>
                    </div>
                    @endif

                    <div class="flex items-center justify-center gap-4 mb-6">
                        <span class="px-3 py-1.5 bg-green-100 text-green-700 rounded text-xs font-bold">bKash</span>
                        <span class="px-3 py-1.5 bg-orange-100 text-orange-700 rounded text-xs font-bold">Nagad</span>
                        <span class="px-3 py-1.5 bg-red-100 text-red-700 rounded text-xs font-bold">Rocket</span>
                        <span class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded text-xs font-bold">Visa</span>
                        <span class="px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded text-xs font-bold">Mastercard</span>
                    </div>

                    <form method="POST" action="{{ route('payment.process', $order) }}">
                        @csrf
                        <button type="submit" class="btn-primary py-3.5 px-10 text-lg inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            Pay ${{ number_format($order->total, 2) }}
                        </button>
                    </form>
                    <p class="text-xs text-gray-400 mt-3">Secured by SSLCommerz &middot; 256-bit SSL Encryption</p>
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <p class="text-gray-500">Payment method not found.</p>
            </div>
        @endif

        <div class="mt-6 pt-4 border-t border-gray-100 text-center">
            <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">Back to Order Details</a>
        </div>
    </div>
</div>
@endsection
