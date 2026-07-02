@extends('layouts.app')
@section('title', 'Payment')
@section('content')
<div class="max-w-2xl mx-auto animate-fade-in-up">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Payment</h1>
    <p class="text-gray-500 mb-8">Complete payment for order <strong>{{ $order->order_number }}</strong></p>

    <div class="bg-white rounded-2xl shadow-md p-6 md:p-8 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h2>
        <div class="flex justify-between py-2 border-b"><span class="text-gray-600">Order Total</span><span class="font-bold text-xl">${{ number_format($order->total, 2) }}</span></div>
        <div class="flex justify-between py-2"><span class="text-gray-600">Status</span><span class="order-status-badge {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ ucfirst($order->payment_status) }}</span></div>
    </div>

    @if($order->payment_status !== 'paid')
        <div class="bg-white rounded-2xl shadow-md p-6 md:p-8">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Card Details</h2>
            <form action="{{ route('payment.process', $order) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 font-medium text-gray-700">Card Number</label>
                    <input type="text" name="card_number" maxlength="16" placeholder="4242424242424242" required class="input-field text-lg tracking-widest font-mono" pattern="[0-9]{16}">
                </div>
                <div class="mb-4">
                    <label class="block mb-2 font-medium text-gray-700">Cardholder Name</label>
                    <input type="text" name="card_name" placeholder="John Doe" required class="input-field">
                </div>
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block mb-2 font-medium text-gray-700">Expiry (MM/YY)</label>
                        <input type="text" name="card_expiry" maxlength="5" placeholder="12/28" required class="input-field" pattern="[0-9]{2}/[0-9]{2}">
                    </div>
                    <div>
                        <label class="block mb-2 font-medium text-gray-700">CVV</label>
                        <input type="text" name="card_cvv" maxlength="3" placeholder="123" required class="input-field" pattern="[0-9]{3}">
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full py-3.5 text-lg flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 0v6m-3-3l3 3 3-3"/></svg>
                    Pay ${{ number_format($order->total, 2) }}
                </button>
                <p class="text-xs text-gray-400 text-center mt-3">Demo: Any 16-digit card number works. No real payment will be processed.</p>
            </form>
        </div>
    @else
        <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
            <svg class="w-12 h-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-green-800 font-bold text-lg">Payment Completed</p>
        </div>
    @endif
</div>
@endsection
