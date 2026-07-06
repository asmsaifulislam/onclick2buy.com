@extends('layouts.app')
@section('title', 'Payment - Order #' . $order->order_number)
@section('content')
<div class="max-w-5xl mx-auto animate-fade-in-up">
    {{-- Status Bar --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="flex items-center gap-2 text-sm">
            <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">1</span>
            <span class="text-gray-600">Cart</span>
        </div>
        <div class="w-8 h-0.5 bg-indigo-600"></div>
        <div class="flex items-center gap-2 text-sm">
            <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">2</span>
            <span class="text-gray-600">Checkout</span>
        </div>
        <div class="w-8 h-0.5 bg-indigo-600"></div>
        <div class="flex items-center gap-2 text-sm">
            <span class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">3</span>
            <span class="font-semibold text-indigo-600">Payment</span>
        </div>
        <div class="w-8 h-0.5 bg-gray-200"></div>
        <div class="flex items-center gap-2 text-sm">
            <span class="w-8 h-8 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center font-bold text-xs">4</span>
            <span class="text-gray-400">Done</span>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Left: Payment Section --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Mobile Verification --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" id="otp-section">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Mobile Verification
                </h2>
                <p class="text-sm text-gray-500 mb-4">Verify your mobile number to proceed with payment</p>
                <div class="flex items-center gap-3" id="otp-input-group">
                    <input type="tel" id="otp-phone" value="{{ $phone }}" placeholder="01XXXXXXXXX" class="input-field flex-1" maxlength="20">
                    <button id="send-otp-btn" class="btn-primary whitespace-nowrap" onclick="sendOtp()">Send OTP</button>
                </div>
                <div id="otp-verify-group" class="hidden mt-4">
                    <div class="flex items-center gap-3">
                        <input type="text" id="otp-code" placeholder="Enter 6-digit OTP" class="input-field flex-1 font-mono text-center text-lg tracking-widest" maxlength="6" inputmode="numeric">
                        <button id="verify-otp-btn" class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2.5 rounded-lg transition-colors whitespace-nowrap" onclick="verifyOtp()">Verify</button>
                    </div>
                    <p id="otp-status" class="text-sm mt-2"></p>
                </div>
            </div>

            {{-- Payment Method Details --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                @if($method)
                    {{-- Mobile Wallet Methods: bKash, Nagad, Rocket --}}
                    @if(in_array($method->code, ['bkash', 'nagad', 'rocket']))
                        <div class="text-center mb-6">
                            <div class="w-20 h-20 mx-auto mb-3 rounded-2xl flex items-center justify-center text-white text-2xl font-bold
                                @switch($method->code)
                                    @case('bkash') bg-gradient-to-br from-pink-500 to-rose-600 @break
                                    @case('nagad') bg-gradient-to-br from-orange-500 to-red-500 @break
                                    @case('rocket') bg-gradient-to-br from-emerald-500 to-teal-600 @break
                                @endswitch
                            ">
                                @switch($method->code)
                                    @case('bkash') &#x09AC;&#x09CD;&#x0995;&#x09BE;&#x09B6; @break
                                    @case('nagad') &#x09A8;&#x0997;&#x09A6; @break
                                    @case('rocket') &#x09B0;&#x0995;&#x09C7;&#x099F; @break
                                @endswitch
                            </div>
                            <h2 class="text-xl font-bold text-gray-900">Pay with {{ $method->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $method->description }}</p>
                        </div>

                        {{-- Merchant Account + QR --}}
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="col-span-2 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl border border-indigo-100 p-4">
                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Send Money To</p>
                                <p class="text-2xl font-bold font-mono text-indigo-700 tracking-wide" id="merchant-number">{{ $method->account_number }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $method->account_name }}</p>
                                <div class="flex gap-2 mt-3">
                                    <button onclick="copyNumber()" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        Copy
                                    </button>
                                    <span class="text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded-lg font-medium">Amount: ${{ number_format($order->total, 2) }}</span>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl border border-gray-200 p-3 flex flex-col items-center justify-center">
                                <div class="w-full aspect-square bg-gray-50 rounded-lg flex items-center justify-center text-gray-300 mb-1">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                </div>
                                <p class="text-[10px] text-gray-400 text-center">Scan to Pay</p>
                            </div>
                        </div>

                        {{-- Instructions --}}
                        @if($method->instructions)
                            <div class="mb-6 bg-gray-50 rounded-xl p-4">
                                <h3 class="font-semibold text-gray-800 text-sm mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    How to Pay
                                </h3>
                                <ol class="space-y-2">
                                    @foreach($method->instructions as $i => $instruction)
                                        <li class="flex items-start gap-3 text-sm text-gray-600">
                                            <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">{{ $i + 1 }}</span>
                                            <span>{{ $instruction }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        @endif

                        {{-- Manual Payment Form --}}
                        <form method="POST" action="{{ route('payment.manual', $order) }}" class="space-y-4 border-t border-gray-100 pt-6">
                            @csrf
                            <input type="hidden" name="phone" id="form-phone" value="{{ $phone }}">
                            <h3 class="font-semibold text-gray-800 text-sm">Enter Payment Details</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-gray-700">Transaction ID <span class="text-red-500">*</span></label>
                                    <input type="text" name="transaction_id" required placeholder="TrxID from your app" class="input-field text-sm" value="{{ old('transaction_id') }}">
                                    @error('transaction_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-gray-700">Sender Number <span class="text-red-500">*</span></label>
                                    <input type="tel" name="sender_number" required placeholder="01XXXXXXXXX" class="input-field text-sm" value="{{ old('sender_number') }}">
                                    @error('sender_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-medium text-gray-700">Your Name</label>
                                <input type="text" name="sender_name" placeholder="Your full name" class="input-field text-sm" value="{{ old('sender_name', Auth::user()?->name) }}">
                            </div>
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition-colors flex items-center justify-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Submit & Verify Payment
                            </button>
                            <p class="text-xs text-gray-400 text-center">Your payment will be verified within 24 hours</p>
                        </form>

                        {{-- Divider --}}
                        <div class="flex items-center gap-3 my-6">
                            <div class="flex-1 h-px bg-gray-200"></div>
                            <span class="text-xs text-gray-400 font-medium">OR PAY WITH GATEWAY</span>
                            <div class="flex-1 h-px bg-gray-200"></div>
                        </div>

                        {{-- SSLCommerz Redirect --}}
                        <form method="POST" action="{{ route('payment.process', $order) }}">
                            @csrf
                            <input type="hidden" name="phone" id="gateway-phone" value="{{ $phone }}">
                            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3.5 rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                Pay with SSLCommerz
                            </button>
                        </form>

                    {{-- Card Payment --}}
                    @elseif($method->code === 'card')
                        <div class="text-center mb-6">
                            <div class="w-20 h-20 mx-auto mb-3 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                <svg class="w-10 h-10 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900">Card Payment</h2>
                            <p class="text-sm text-gray-500">Visa, Mastercard & Debit Cards accepted</p>
                        </div>

                        {{-- Card Icons --}}
                        <div class="flex items-center justify-center gap-4 mb-6">
                            <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-bold border border-blue-100">VISA</span>
                            <span class="px-4 py-2 bg-yellow-50 text-yellow-700 rounded-lg text-sm font-bold border border-yellow-100">MasterCard</span>
                            <span class="px-4 py-2 bg-gray-50 text-gray-700 rounded-lg text-sm font-bold border border-gray-200">Debit</span>
                        </div>

                        {{-- Card Form --}}
                        <form method="POST" action="{{ route('payment.process', $order) }}" class="space-y-4" id="card-form">
                            @csrf
                            <input type="hidden" name="phone" id="form-phone-card" value="{{ $phone }}">
                            <div class="relative">
                                <label class="block mb-1 text-xs font-medium text-gray-700">Card Number <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="text" name="card_number" id="card-number" required maxlength="19" placeholder="1234 5678 9012 3456" class="input-field text-sm font-mono pl-10 pr-24" value="{{ old('card_number') }}" oninput="formatCardNumber(this)">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                                    <button type="button" onclick="document.getElementById('card-scanner-input').click()" class="absolute right-2 top-1/2 -translate-y-1/2 text-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-2.5 py-1 rounded font-medium transition-colors flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                        Scan
                                    </button>
                                    <input type="file" id="card-scanner-input" accept="image/*" capture="environment" class="hidden" onchange="scanCard(event)">
                                </div>
                                @error('card_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-medium text-gray-700">Cardholder Name <span class="text-red-500">*</span></label>
                                <input type="text" name="card_name" required placeholder="Name on card" class="input-field text-sm" value="{{ old('card_name') }}">
                                @error('card_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-gray-700">Expiry <span class="text-red-500">*</span></label>
                                    <input type="text" name="card_expiry" required maxlength="5" placeholder="MM/YY" class="input-field text-sm" value="{{ old('card_expiry') }}" oninput="formatExpiry(this)">
                                    @error('card_expiry')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block mb-1 text-xs font-medium text-gray-700">CVV <span class="text-red-500">*</span></label>
                                    <input type="password" name="card_cvv" required maxlength="4" placeholder="***" class="input-field text-sm" value="{{ old('card_cvv') }}" inputmode="numeric">
                                    @error('card_cvv')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3.5 rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Pay ${{ number_format($order->total, 2) }}
                            </button>
                        </form>

                    {{-- COD --}}
                    @elseif($method->code === 'cod')
                        <div class="text-center py-8">
                            <div class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 mb-2">Cash on Delivery</h2>
                            <p class="text-gray-500 mb-2">Pay when you receive your order</p>
                            <p class="text-sm text-gray-400 mb-6">No advance payment needed. Our delivery agent will collect the payment.</p>
                            <form method="POST" action="{{ route('payment.process', $order) }}">
                                @csrf
                                <input type="hidden" name="phone" value="{{ $phone }}">
                                <button type="submit" class="btn-primary py-3 px-10">Confirm Order</button>
                            </form>
                        </div>
                    @endif
                @else
                    <div class="text-center py-8 text-gray-500">Payment method not found.</div>
                @endif
            </div>
        </div>

        {{-- Right: Order Summary --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h2>
                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                        {{ $order->items->count() }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Order {{ $order->order_number }}</p>
                        <p class="text-xs text-gray-500">{{ $order->items->count() }} item(s)</p>
                    </div>
                </div>
                <div class="space-y-3 mb-4">
                    @foreach($order->items as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 truncate max-w-[70%]">{{ $item->product_name }} &times; {{ $item->quantity }}</span>
                            <span class="font-medium text-gray-800">${{ number_format($item->price * $item->quantity, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-gray-100 pt-3 space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-500"><span>Subtotal</span><span>${{ number_format($order->total, 2) }}</span></div>
                    <div class="flex justify-between text-gray-500"><span>Shipping</span><span class="text-green-600 font-medium">Free</span></div>
                    <div class="flex justify-between text-lg font-extrabold text-gray-900 pt-2 border-t border-gray-200 mt-2">
                        <span>Total</span>
                        <span>${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>

                {{-- Security Badges --}}
                <div class="mt-6 pt-4 border-t border-gray-100 space-y-2">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        <span>256-bit SSL Encrypted</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>Verified by SSLCommerz</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>PCI DSS Compliant</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>OTP Verified Transaction</span>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100">
                    <a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center gap-1 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back to Order
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let otpVerified = {{ $otpVerified ? 'true' : 'false' }};
const phoneInput = document.getElementById('otp-phone');
const otpInputGroup = document.getElementById('otp-input-group');
const otpVerifyGroup = document.getElementById('otp-verify-group');
const sendBtn = document.getElementById('send-otp-btn');
const verifyBtn = document.getElementById('verify-otp-btn');
const otpStatus = document.getElementById('otp-status');
const formPhone = document.getElementById('form-phone');
const gatewayPhone = document.getElementById('gateway-phone');
const formPhoneCard = document.getElementById('form-phone-card');

if (otpVerified) {
    otpInputGroup.classList.add('hidden');
    otpVerifyGroup.classList.remove('hidden');
    otpVerifyGroup.innerHTML = '<div class="flex items-center gap-2 text-green-600"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Phone verified</div>';
}

function sendOtp() {
    const phone = phoneInput.value.trim();
    if (!phone || phone.length < 11) {
        alert('Please enter a valid phone number');
        return;
    }
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Sending...';
    fetch('{{ route("otp.send") }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({phone})
    }).then(r => r.json()).then(data => {
        if (data.sent) {
            otpInputGroup.classList.add('hidden');
            otpVerifyGroup.classList.remove('hidden');
            otpStatus.innerHTML = '<span class="text-green-600">OTP sent to ' + data.message + '</span>';
            if (data.otp) {
                otpStatus.innerHTML += ' <span class="text-gray-400">(Dev: ' + data.otp + ')</span>';
            }
            updatePhoneFields(phone);
        }
    }).catch(() => {
        alert('Failed to send OTP');
    }).finally(() => {
        sendBtn.disabled = false;
        sendBtn.innerHTML = 'Send OTP';
    });
}

function verifyOtp() {
    const phone = phoneInput.value.trim();
    const otp = document.getElementById('otp-code').value.trim();
    if (!otp || otp.length !== 6) {
        otpStatus.innerHTML = '<span class="text-red-500">Please enter the 6-digit OTP</span>';
        return;
    }
    verifyBtn.disabled = true;
    verifyBtn.innerHTML = 'Verifying...';
    fetch('{{ route("otp.verify") }}', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({phone, otp})
    }).then(r => r.json()).then(data => {
        if (data.verified) {
            otpVerified = true;
            otpStatus.innerHTML = '<span class="text-green-600 flex items-center gap-1"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Phone verified successfully!</span>';
            verifyBtn.innerHTML = 'Verified';
            verifyBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            verifyBtn.classList.add('bg-green-100', 'text-green-700', 'cursor-default');
            verifyBtn.disabled = true;
        } else {
            otpStatus.innerHTML = '<span class="text-red-500">' + (data.message || 'Invalid OTP') + '</span>';
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = 'Verify';
        }
    }).catch(err => {
        otpStatus.innerHTML = '<span class="text-red-500">Invalid or expired OTP</span>';
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = 'Verify';
    });
}

function updatePhoneFields(phone) {
    if (formPhone) formPhone.value = phone;
    if (gatewayPhone) gatewayPhone.value = phone;
    if (formPhoneCard) formPhoneCard.value = phone;
}

function copyNumber() {
    const num = document.getElementById('merchant-number').textContent;
    navigator.clipboard.writeText(num).then(() => {
        const btn = event.target.closest('button');
        const orig = btn.innerHTML;
        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copied!';
        setTimeout(() => btn.innerHTML = orig, 2000);
    });
}

function formatCardNumber(input) {
    let val = input.value.replace(/\D/g, '');
    val = val.replace(/(.{4})/g, '$1 ').trim();
    input.value = val;
}

function formatExpiry(input) {
    let val = input.value.replace(/\D/g, '');
    if (val.length >= 2) val = val.substring(0, 2) + '/' + val.substring(2);
    input.value = val;
}

function scanCard(event) {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        alert('Card image captured. Please enter the card number manually for security.\n\nCard scanning via camera will be available in the app version.');
    };
    reader.readAsDataURL(file);
}
</script>
@endpush
