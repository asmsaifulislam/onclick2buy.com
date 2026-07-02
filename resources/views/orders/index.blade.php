@extends('layouts.app')
@section('title', 'My Orders')
@section('content')
    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-8 animate-fade-in-up">My Orders</h1>

    @if($orders->isEmpty())
        <div class="text-center py-16 animate-fade-in">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-xl text-gray-500 mb-4">You haven't placed any orders yet.</p>
            <a href="{{ route('products.index') }}" class="btn-primary inline-flex items-center gap-2">Start Shopping</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-white rounded-xl shadow-md p-5 sm:p-6 animate-fade-in-up hover:shadow-lg transition-shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="font-bold text-gray-900">{{ $order->order_number }}</h3>
                                <span class="order-status-badge inline-flex items-center gap-1
                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $order->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $order->status === 'shipped' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $order->status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">{{ $order->items->count() }} item(s) &middot; {{ $order->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-xl font-extrabold text-gray-900">${{ number_format($order->total, 2) }}</span>
                            <a href="{{ route('orders.show', $order) }}" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-medium hover:bg-indigo-100 transition-colors">View Details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-8 pagination-custom">{{ $orders->links() }}</div>
    @endif
@endsection
