@extends('layouts.app')
@section('title', 'Order ' . $order->order_number)
@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 animate-fade-in-up">
        <div>
            <a href="{{ route('orders.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to My Orders
            </a>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Order {{ $order->order_number }}</h1>
        </div>
        @if($order->payment_status === 'unpaid')
            <a href="{{ route('payment.show', $order) }}" class="btn-primary inline-flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Pay Now
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 animate-fade-in-up animate-delay-1">
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h2 class="font-bold text-lg text-gray-900 mb-3">Order Details</h2>
            <div class="space-y-2 text-gray-600 text-sm">
                <p><span class="font-medium text-gray-800">Status:</span>
                    <span class="order-status-badge inline-flex items-center gap-1 {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}{{ $order->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}{{ $order->status === 'shipped' ? 'bg-purple-100 text-purple-800' : '' }}{{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}{{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </p>
                <p><span class="font-medium text-gray-800">Payment:</span> {{ ucwords(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</p>
                <p><span class="font-medium text-gray-800">Payment Status:</span> {{ ucfirst($order->payment_status ?? 'Unpaid') }}</p>
                <p><span class="font-medium text-gray-800">Date:</span> {{ $order->created_at->format('M d, Y h:i A') }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h2 class="font-bold text-lg text-gray-900 mb-3">Shipping Address</h2>
            <p class="text-gray-600 text-sm">{{ $order->shipping_address }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6 animate-fade-in-up animate-delay-2">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-bold text-lg text-gray-900">Order Items</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr class="table-row border-b border-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($item->product && $item->product->images && count($item->product->images) > 0)
                                        <img src="{{ $item->product->images[0] }}" class="h-10 w-10 rounded-lg object-cover">
                                    @endif
                                    <span class="font-medium text-gray-800">{{ $item->product_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">${{ number_format($item->price, 2) }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-800">${{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50">
                        <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-800">Total</td>
                        <td class="px-6 py-4 font-bold text-gray-900 text-lg">${{ number_format($order->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
