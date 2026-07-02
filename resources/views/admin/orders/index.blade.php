@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
    <h1 class="text-3xl font-extrabold text-gray-900 mb-6 animate-fade-in-up">Orders</h1>
    <div class="bg-white rounded-xl shadow-md overflow-hidden animate-fade-in-up animate-delay-1">
        <div class="overflow-x-auto">
            <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Order #</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="table-row border-b border-gray-50">
                        <td class="px-5 py-4 font-medium text-gray-800">{{ $order->order_number }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $order->user->name }}</td>
                        <td class="px-5 py-4 font-semibold text-gray-800">${{ number_format($order->total, 2) }}</td>
                        <td class="px-5 py-4">
                            <span class="order-status-badge inline-flex items-center gap-1 
                                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $order->status === 'shipped' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                <span class="w-1.5 h-1.5 rounded-full 
                                    {{ $order->status === 'pending' ? 'bg-yellow-500' : '' }}
                                    {{ $order->status === 'processing' ? 'bg-blue-500' : '' }}
                                    {{ $order->status === 'shipped' ? 'bg-purple-500' : '' }}
                                    {{ $order->status === 'delivered' ? 'bg-green-500' : '' }}
                                    {{ $order->status === 'cancelled' ? 'bg-red-500' : '' }}"></span>
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.orders.show', $order) }}" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-medium hover:bg-indigo-100 transition-colors inline-flex items-center gap-1">
                                View Details
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-400">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    <div class="mt-6 pagination-custom">{{ $orders->links() }}</div>
@endsection