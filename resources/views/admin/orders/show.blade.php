@extends('layouts.admin')
@section('title', 'Order ' . $order->order_number)
@section('content')
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 animate-fade-in-up">
        <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Order {{ $order->order_number }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn-outline text-sm py-1.5 px-4 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Orders
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6 animate-fade-in-up animate-delay-1">
        <div class="admin-card bg-white p-6 rounded-xl shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <h2 class="font-bold text-lg text-gray-900">Customer Details</h2>
            </div>
            <div class="space-y-2 text-gray-600">
                <p><span class="font-medium text-gray-800">Name:</span> {{ $order->user->name }}</p>
                <p><span class="font-medium text-gray-800">Email:</span> {{ $order->user->email }}</p>
            </div>
        </div>
        <div class="admin-card bg-white p-6 rounded-xl shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h2 class="font-bold text-lg text-gray-900">Order Details</h2>
            </div>
            <div class="space-y-2 text-gray-600">
                <p><span class="font-medium text-gray-800">Total:</span> ${{ number_format($order->total, 2) }}</p>
                <p><span class="font-medium text-gray-800">Status:</span> 
                    <span class="order-status-badge inline-flex items-center gap-1 {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}{{ $order->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}{{ $order->status === 'shipped' ? 'bg-purple-100 text-purple-800' : '' }}{{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}{{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </p>
                <p><span class="font-medium text-gray-800">Payment:</span> {{ ucwords(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</p>
                <p><span class="font-medium text-gray-800">Date:</span> {{ $order->created_at->format('M d, Y h:i A') }}</p>
            </div>
        </div>
    </div>

    <div class="admin-card bg-white p-6 rounded-xl shadow-md mb-6 animate-fade-in-up animate-delay-2">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h2 class="font-bold text-lg text-gray-900">Shipping Address</h2>
        </div>
        <p class="text-gray-600">{{ $order->shipping_address }}</p>
        @if($order->notes)<p class="mt-3 pt-3 border-t text-gray-600"><span class="font-medium text-gray-800">Notes:</span> {{ $order->notes }}</p>@endif
    </div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6 animate-fade-in-up animate-delay-3">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <h2 class="font-bold text-lg text-gray-900">Order Items</h2>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr class="table-row border-b border-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $item->product_name }}</td>
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

    <div class="admin-card bg-white p-6 rounded-xl shadow-md animate-fade-in-up animate-delay-3">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </div>
            <h2 class="font-bold text-lg text-gray-900">Update Status</h2>
        </div>
        <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            @csrf @method('PATCH')
            <select name="status" class="input-field sm:w-48">
                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn-primary text-sm py-2.5">Update Status</button>
        </form>
    </div>
@endsection