@extends('layouts.admin')
@section('title', 'Payment Details')
@section('page_title', 'Payment Details')
@section('content')
<div class="max-w-3xl">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-gray-800 rounded-xl p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-400 block">Order</span>
                <span class="text-white font-medium">{{ $payment->order->order_number ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-gray-400 block">Amount</span>
                <span class="text-white font-bold">${{ number_format($payment->amount, 2) }}</span>
            </div>
            <div>
                <span class="text-gray-400 block">Payment Method</span>
                <span class="text-white">{{ $payment->method?->name ?? $payment->method_code }}</span>
            </div>
            <div>
                <span class="text-gray-400 block">Status</span>
                <span class="inline-block mt-1">
                    @if($payment->status === 'verified')
                        <span class="text-green-400 bg-green-900/30 px-2 py-0.5 rounded-full text-xs font-medium">Verified</span>
                    @elseif($payment->status === 'failed')
                        <span class="text-red-400 bg-red-900/30 px-2 py-0.5 rounded-full text-xs font-medium">Failed</span>
                    @else
                        <span class="text-yellow-400 bg-yellow-900/30 px-2 py-0.5 rounded-full text-xs font-medium">Pending</span>
                    @endif
                </span>
            </div>
            @if($payment->transaction_id)
                <div>
                    <span class="text-gray-400 block">Transaction ID</span>
                    <span class="text-white font-mono text-xs">{{ $payment->transaction_id }}</span>
                </div>
            @endif
            @if($payment->sender_number)
                <div>
                    <span class="text-gray-400 block">Sender Number</span>
                    <span class="text-white">{{ $payment->sender_number }}</span>
                </div>
            @endif
            @if($payment->sender_name)
                <div>
                    <span class="text-gray-400 block">Sender Name</span>
                    <span class="text-white">{{ $payment->sender_name }}</span>
                </div>
            @endif
            @if($payment->verified_at)
                <div>
                    <span class="text-gray-400 block">Verified At</span>
                    <span class="text-white">{{ $payment->verified_at->format('M d, Y h:i A') }}</span>
                </div>
            @endif
            @if($payment->verifier)
                <div>
                    <span class="text-gray-400 block">Verified By</span>
                    <span class="text-white">{{ $payment->verifier->name }}</span>
                </div>
            @endif
            <div>
                <span class="text-gray-400 block">Created</span>
                <span class="text-white">{{ $payment->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>

        @if($payment->notes)
            <div>
                <span class="text-gray-400 block text-sm">Customer Notes</span>
                <p class="text-white text-sm mt-1">{{ $payment->notes }}</p>
            </div>
        @endif

        @if($payment->admin_notes)
            <div>
                <span class="text-gray-400 block text-sm">Admin Notes</span>
                <p class="text-white text-sm mt-1">{{ $payment->admin_notes }}</p>
            </div>
        @endif
    </div>

    @if($payment->status === 'pending')
        <div class="bg-gray-800 rounded-xl p-6 mt-4 space-y-4">
            <h3 class="text-lg font-bold text-white">Verify Payment</h3>
            <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block mb-1 font-medium text-gray-300">Admin Notes (optional)</label>
                    <textarea name="admin_notes" rows="2" class="input-field w-full" placeholder="Payment verified, looks good."></textarea>
                </div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2 rounded-lg transition-colors">
                    Approve & Verify
                </button>
            </form>
            <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="space-y-3 pt-3 border-t border-gray-700">
                @csrf
                <div>
                    <label class="block mb-1 font-medium text-gray-300">Reason for Rejection <span class="text-red-400">*</span></label>
                    <textarea name="admin_notes" rows="2" class="input-field w-full" required placeholder="Transaction ID not found in our system."></textarea>
                </div>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium px-6 py-2 rounded-lg transition-colors">
                    Reject Payment
                </button>
            </form>
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">View Order #{{ $payment->order->order_number }}</a>
    </div>
</div>
@endsection
