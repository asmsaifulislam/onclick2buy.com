@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 animate-fade-in-up">Dashboard</h1>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8 animate-fade-in-up">
        <div class="stat-card p-6 rounded-xl shadow-lg">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-indigo-200 text-sm font-medium">Total Products</h3>
                <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-white">{{ $totalProducts }}</p>
        </div>
        <div class="stat-card p-6 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #065f46, #047857);">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-emerald-200 text-sm font-medium">Categories</h3>
                <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-white">{{ $totalCategories }}</p>
        </div>
        <div class="stat-card p-6 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #1e40af, #2563eb);">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-blue-200 text-sm font-medium">Total Orders</h3>
                <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-white">{{ $totalOrders }}</p>
        </div>
        <div class="stat-card p-6 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #6d28d9, #7c3aed);">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-purple-200 text-sm font-medium">Revenue</h3>
                <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-white">${{ number_format($totalRevenue, 2) }}</p>
        </div>
    </div>
    <div class="animate-fade-in-up animate-delay-2">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Recent Orders</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-indigo-600 hover:text-indigo-700 font-medium text-sm flex items-center gap-1 transition-colors">View All <span>&rarr;</span></a>
        </div>
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Order #</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-5 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
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
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
@endsection