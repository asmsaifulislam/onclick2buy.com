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
            @if(in_array($method->code, ['bkash', 'nagad', 'rocket']))
                {{-- Mobile Banking Payment Form --}}
                <div class="mb-6 p-4 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl border border-indigo-100">
                    <h2 class="text-lg font-bold text-gray-800 mb-3">Pay with {{ $method->name }}</h2>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p><span class="font-semibold">Merchant Account:</span> <span class="text-lg font-mono font-bold text-indigo-700">{{ $method->account_number }}</span></p>
                        <p><span class="font-semibold">Account Name:</span> {{ $method->account_name }}</p>
                        <p><span class="font-semibold">Amount:</span> ${{ number_format($order->total, 2) }}</p>
                    </div>
                </div>

                @if($method->instructions)
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-800 mb-2">Instructions:</h3>
                        <ol class="list-decimal list-inside space-y-1 text-sm text-gray-600">
                            @foreach($method->instructions as $instruction)
                                <li>{{ $instruction }}</li>
                            @endforeach
                        </ol>
                    </div>
                @endif

                <form method="POST" action="{{ route('payment.process', $order) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Transaction ID <span class="text-red-500">*</span></label>
                        <input type="text" name="transaction_id" required placeholder="Enter the TrxID from your app" class="input-field" value="{{ old('transaction_id') }}">
                        @error('transaction_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Your Mobile Number <span class="text-red-500">*</span></label>
                        <input type="text" name="sender_number" required placeholder="01XXXXXXXXX" class="input-field" value="{{ old('sender_number') }}">
                        @error('sender_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Your Name</label>
                        <input type="text" name="sender_name" placeholder="Your full name" class="input-field" value="{{ old('sender_name', Auth::user()?->name) }}">
                    </div>
                    <button type="submit" class="btn-primary w-full py-3.5 text-lg flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Submit Payment
                    </button>
                </form>
                <p class="text-xs text-gray-400 mt-3 text-center">Your payment will be verified by admin before order processing.</p>

            @elseif($method->code === 'card')
                {{-- Card Payment Form --}}
                <div class="mb-6 p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                    <div class="flex items-center gap-4 mb-3">
                        <svg class="w-10 h-10 text-blue-600" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Card Payment</h2>
                            <p class="text-xs text-gray-500">We accept Visa, Mastercard & Debit Cards</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <span class="px-2 py-1 bg-white rounded text-xs font-bold text-gray-600 border">VISA</span>
                        <span class="px-2 py-1 bg-white rounded text-xs font-bold text-gray-600 border">MasterCard</span>
                        <span class="px-2 py-1 bg-white rounded text-xs font-bold text-gray-600 border">Debit</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('payment.process', $order) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Card Number <span class="text-red-500">*</span></label>
                        <input type="text" name="card_number" required maxlength="16" placeholder="1234 5678 9012 3456" class="input-field font-mono" value="{{ old('card_number') }}">
                        @error('card_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Cardholder Name <span class="text-red-500">*</span></label>
                        <input type="text" name="card_name" required placeholder="Name on card" class="input-field" value="{{ old('card_name') }}">
                        @error('card_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">Expiry (MM/YY) <span class="text-red-500">*</span></label>
                            <input type="text" name="card_expiry" required maxlength="5" placeholder="MM/YY" class="input-field" value="{{ old('card_expiry') }}">
                            @error('card_expiry')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block mb-1 font-medium text-gray-700">CVV <span class="text-red-500">*</span></label>
                            <input type="text" name="card_cvv" required maxlength="3" placeholder="123" class="input-field" value="{{ old('card_cvv') }}">
                            @error('card_cvv')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <button type="submit" class="btn-primary w-full py-3.5 text-lg flex items-center justify-center gap-2">
                        Pay ${{ number_format($order->total, 2) }}
                    </button>
                </form>

            @elseif($method->code === 'cod')
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Cash on Delivery</h2>
                    <p class="text-gray-500 mb-6">Pay when you receive your order. No advance payment needed.</p>
                    <form method="POST" action="{{ route('payment.process', $order) }}">
                        @csrf
                        <button type="submit" class="btn-primary py-3 px-8">
                            Confirm COD Order
                        </button>
                    </form>
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
