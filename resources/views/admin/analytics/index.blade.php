@extends('layouts.admin')
@section('title', 'Analytics')
@section('content')
<div class="mb-6 animate-fade-in-up">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-3xl font-extrabold text-gray-900">Business Analytics</h1>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex flex-wrap items-center gap-1 bg-white rounded-xl shadow-sm p-1 border">
                @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $k => $v)
                    <button type="submit" name="period" value="{{ $k }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ ($range['period'] ?? 'monthly') === $k ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100' }}">{{ $v }}</button>
                @endforeach
            </form>
            <a href="{{ route('admin.export.analytics', ['period' => $range['period'] ?? 'monthly']) }}" class="p-2.5 bg-white border rounded-xl hover:bg-gray-50 transition-colors" title="Export CSV">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </a>
        </div>
    </div>
    <p class="text-gray-500 mt-1">{{ $range['label'] }}</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8 animate-fade-in-up animate-delay-1">
    <div class="bg-white p-5 rounded-xl shadow-md border-l-4 border-indigo-500">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Revenue</p>
        <p class="text-2xl font-extrabold text-gray-900 mt-1">${{ number_format($totalRevenue, 2) }}</p>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-md border-l-4 border-blue-500">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Orders</p>
        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $totalOrders }}</p>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-md border-l-4 border-green-500">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Avg Order Value</p>
        <p class="text-2xl font-extrabold text-gray-900 mt-1">${{ number_format($avgOrderValue, 2) }}</p>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-md border-l-4 border-purple-500">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending Orders</p>
        <p class="text-2xl font-extrabold {{ $pendingOrders > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ $pendingOrders }}</p>
    </div>
    <div class="bg-white p-5 rounded-xl shadow-md border-l-4 border-rose-500">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Processing</p>
        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $processingOrders }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-2">
        <h2 class="font-bold text-gray-900 mb-4">Revenue Trend</h2>
        @if($chartLabels->isNotEmpty())
            <div class="space-y-2">
                @php $maxVal = $chartValues->max(); @endphp
                @foreach($chartLabels as $i => $label)
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 w-16 sm:w-20 text-right truncate">{{ $label }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-6 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-1000" style="width: {{ $maxVal > 0 ? ($chartValues[$i] / $maxVal) * 100 : 0 }}%"></div>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 w-24">${{ number_format($chartValues[$i], 0) }}</span>
                        <span class="text-xs text-gray-400 w-16">({{ $chartCounts[$i] }})</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-center py-8">No data for this period.</p>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-2">
        <h2 class="font-bold text-gray-900 mb-4">Revenue by Category</h2>
        @if($categoryLabels->isNotEmpty())
            @php $catMax = $categoryValues->max(); @endphp
            <div class="space-y-3">
                @foreach($categoryLabels as $i => $label)
                    <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600 w-20 sm:w-32 truncate">{{ $label }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-7 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full transition-all duration-1000" style="width: {{ $catMax > 0 ? ($categoryValues[$i] / $catMax) * 100 : 0 }}%"></div>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 w-24">${{ number_format($categoryValues[$i], 0) }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-center py-8">No data for this period.</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-3">
        <h2 class="font-bold text-gray-900 mb-4">Top Selling Products</h2>
        @if($topProducts->isNotEmpty())
            <div class="space-y-3">
                @foreach($topProducts as $i => $p)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                            <span class="text-sm font-medium text-gray-800 truncate max-w-[180px]">{{ $p->product_name }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">{{ $p->total_qty }} sold</p>
                            <p class="text-xs text-gray-500">${{ number_format($p->total_revenue, 0) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-center py-8">No sales data yet.</p>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-3">
        <h2 class="font-bold text-gray-900 mb-4">Stock Summary</h2>
        <div class="space-y-4">
            <div class="flex justify-between items-center p-3 bg-indigo-50 rounded-lg">
                <span class="font-medium text-indigo-800">Total Stock</span>
                <span class="text-xl font-bold text-indigo-700">{{ $stockSummary['total'] }}</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-amber-50 rounded-lg">
                <span class="font-medium text-amber-800">Low Stock (&le;5)</span>
                <span class="text-xl font-bold text-amber-700">{{ $stockSummary['low_stock'] }}</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                <span class="font-medium text-red-800">Out of Stock</span>
                <span class="text-xl font-bold text-red-700">{{ $stockSummary['out_of_stock'] }}</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                <span class="font-medium text-green-800">Inventory Value</span>
                <span class="text-xl font-bold text-green-700">${{ number_format($stockSummary['total_value'], 0) }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-3">
        <h2 class="font-bold text-gray-900 mb-4">Top Customers</h2>
        @if($topCustomers->isNotEmpty())
            <div class="space-y-3">
                @foreach($topCustomers as $i => $c)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $c->user->name ?? 'Deleted' }}</p>
                                <p class="text-xs text-gray-400">{{ $c->order_count }} orders</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-gray-900">${{ number_format($c->total_spent, 0) }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-center py-8">No customer data yet.</p>
        @endif
    </div>
</div>
@endsection
