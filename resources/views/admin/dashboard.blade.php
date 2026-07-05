@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8 animate-fade-in-up">Dashboard</h1>

    {{-- Stats Cards --}}
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
                    <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
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

    {{-- Automation Hub Quick Access --}}
    <div class="mb-8 animate-fade-in-up animate-delay-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Automation Hub</h2>
            <a href="{{ route('admin.automation-hub') }}" class="text-indigo-600 hover:text-indigo-700 font-medium text-sm flex items-center gap-1 transition-colors">View All <span>&rarr;</span></a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Google Analytics --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Analytics</p>
                        <p class="text-xs text-green-600">Active</p>
                    </div>
                </div>
            </div>
            {{-- Prometheus --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Monitoring</p>
                        <p class="text-xs text-green-600">Active</p>
                    </div>
                </div>
            </div>
            {{-- Mautic --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Marketing</p>
                        <p class="text-xs text-green-600">Active</p>
                    </div>
                </div>
            </div>
            {{-- ERPNext --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">ERP</p>
                        <p class="text-xs text-green-600">Active</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
            {{-- SSL --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">SSL/HTTPS</p>
                        <p class="text-xs text-green-600">Secured</p>
                    </div>
                </div>
            </div>
            {{-- AI Agents --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">AI Chat</p>
                        <p class="text-xs text-green-600">Active</p>
                    </div>
                </div>
            </div>
            {{-- Recommendations --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Recommendations</p>
                        <p class="text-xs text-green-600">Active</p>
                    </div>
                </div>
            </div>
            {{-- Live Chat --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">Live Chat</p>
                        <p class="text-xs text-green-600">Online</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Orders --}}
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
