@extends('layouts.admin')
@section('title', 'Payments')
@section('page_title', 'Payment History')
@section('content')
<div class="bg-gray-800 rounded-xl overflow-hidden">
    <div class="p-4 border-b border-gray-700 flex flex-wrap gap-3">
        <form method="GET" class="flex flex-wrap gap-3 items-center">
            <select name="method_code" class="input-field text-sm py-1.5" onchange="this.form.submit()">
                <option value="">All Methods</option>
                @foreach($methods as $m)
                    <option value="{{ $m->code }}" {{ request('method_code') == $m->code ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
            </select>
            <select name="status" class="input-field text-sm py-1.5" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
            @if(request('status') || request('method_code'))
                <a href="{{ route('admin.payments.index') }}" class="text-sm text-gray-400 hover:text-white">Clear</a>
            @endif
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-700 text-gray-300 text-left">
                <tr>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Method</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">TrxID</th>
                    <th class="px-4 py-3">Sender</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($payments as $payment)
                    <tr class="hover:bg-gray-750">
                        <td class="px-4 py-3 text-white font-medium">{{ $payment->order->order_number ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-gray-300">{{ $payment->method?->name ?? $payment->method_code }}</td>
                        <td class="px-4 py-3 text-white">${{ number_format($payment->amount, 2) }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-300">{{ $payment->transaction_id ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-300">{{ $payment->sender_number ?? $payment->sender_name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($payment->status === 'verified')
                                <span class="text-green-400 bg-green-900/30 px-2 py-0.5 rounded-full text-xs font-medium">Verified</span>
                            @elseif($payment->status === 'failed')
                                <span class="text-red-400 bg-red-900/30 px-2 py-0.5 rounded-full text-xs font-medium">Failed</span>
                            @else
                                <span class="text-yellow-400 bg-yellow-900/30 px-2 py-0.5 rounded-full text-xs font-medium">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $payment->created_at->format('M d, H:i') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.payments.show', $payment) }}" class="text-indigo-400 hover:text-indigo-300 text-xs font-medium">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-700">
        {{ $payments->links() }}
    </div>
</div>
@endsection
