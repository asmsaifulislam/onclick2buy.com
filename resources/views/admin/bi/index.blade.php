@extends('layouts.admin')
@section('title', 'BI Dashboard')
@section('content')
<div class="mb-6 animate-fade-in-up">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900">360&deg; BI Dashboard</h1>
            <p class="text-gray-500 mt-1">{{ $range['label'] }} &middot; {{ $totalOrders }} orders &middot; ${{ number_format($totalRevenue, 2) }} revenue</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex flex-wrap items-center gap-1 bg-white rounded-xl shadow-sm p-1 border">
                <input type="hidden" name="product" value="{{ $productId }}">
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <input type="hidden" name="buyer" value="{{ $buyerId }}">
                @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $k => $v)
                    <button type="submit" name="period" value="{{ $k }}" class="px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $period === $k ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100' }}">{{ $v }}</button>
                @endforeach
            </form>
            <a href="#" onclick="window.print()" class="p-2.5 bg-white border rounded-xl hover:bg-gray-50 transition-colors" title="Print">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            </a>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md p-4 mb-8 animate-fade-in-up animate-delay-1">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <input type="hidden" name="period" value="{{ $period }}">
        <div class="min-w-[180px] flex-1">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Product</label>
            <select name="product" class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Products</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ $productId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[160px] flex-1">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Order Status</label>
            <select name="status" class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" {{ $statusFilter === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[160px] flex-1">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Buyer</label>
            <select name="buyer" class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Buyers</option>
                @foreach($buyers as $b)
                    <option value="{{ $b->id }}" {{ $buyerId == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->email }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2 pb-0.5 flex-1 min-w-[200px]">
            <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">Query</button>
            <a href="{{ route('admin.bi.index') }}" class="flex-1 px-4 py-2.5 text-center text-sm font-medium text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border border-gray-200">Clear</a>
        </div>
    </form>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3 mb-8 animate-fade-in-up animate-delay-1">
    <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-indigo-500">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Revenue</p>
        <p class="text-xl font-extrabold text-gray-900 mt-1">${{ number_format($totalRevenue, 2) }}</p>
        @if($growthPercent != 0)
            <p class="text-xs {{ $growthPercent >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ $growthPercent >= 0 ? '+' : '' }}{{ $growthPercent }}%</p>
        @endif
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-blue-500">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Orders</p>
        <p class="text-xl font-extrabold text-gray-900 mt-1">{{ $totalOrders }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-green-500">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Avg Order</p>
        <p class="text-xl font-extrabold text-gray-900 mt-1">${{ number_format($avgOrderValue, 2) }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-purple-500">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Customers</p>
        <p class="text-xl font-extrabold text-gray-900 mt-1">{{ $totalCustomers }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-amber-500">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending</p>
        <p class="text-xl font-extrabold text-gray-900 mt-1">{{ $pendingOrders }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-rose-500">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Cancelled</p>
        <p class="text-xl font-extrabold text-gray-900 mt-1">{{ $cancelledOrders }}</p>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md border-l-4 border-teal-500">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Products</p>
        <p class="text-xl font-extrabold text-gray-900 mt-1">{{ $totalProducts }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-2">
        <h2 class="font-bold text-gray-900 mb-4">Revenue Trend</h2>
        <canvas id="revenueChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-2">
        <h2 class="font-bold text-gray-900 mb-4">Revenue by Category</h2>
        <canvas id="categoryChart" height="200"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-2">
        <h2 class="font-bold text-gray-900 mb-4">Top Selling Products</h2>
        <canvas id="topProductsChart" height="250"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-2">
        <h2 class="font-bold text-gray-900 mb-4">Sales by Hour of Day</h2>
        <canvas id="hourChart" height="250"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-2">
        <h2 class="font-bold text-gray-900 mb-4">Sales by Day of Week</h2>
        <canvas id="dayChart" height="250"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-3">
        <h2 class="font-bold text-gray-900 mb-4">Payment Methods</h2>
        <canvas id="paymentChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-3">
        <h2 class="font-bold text-gray-900 mb-4">Order Status Distribution</h2>
        <canvas id="statusChart" height="220"></canvas>
    </div>
</div>

@if($selectedProduct && $productStatusBreakdown->isNotEmpty())
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-3">
        <h2 class="font-bold text-gray-900 mb-4">Status Breakdown: {{ $selectedProduct->name }}</h2>
        <canvas id="productStatusChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-3">
        <h2 class="font-bold text-gray-900 mb-4">Buyers: {{ $selectedProduct->name }}</h2>
        <div class="overflow-y-auto max-h-64">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 uppercase text-xs">
                        <th class="pb-2">Buyer</th>
                        <th class="pb-2 text-right">Qty</th>
                        <th class="pb-2 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($buyerBreakdown as $bb)
                        <tr class="border-t border-gray-50">
                            <td class="py-2">
                                <p class="text-gray-800 font-medium">{{ $bb->name }}</p>
                                <p class="text-xs text-gray-400">{{ $bb->email }}</p>
                            </td>
                            <td class="py-2 text-right text-gray-600">{{ $bb->qty }}</td>
                            <td class="py-2 text-right font-semibold text-gray-900">${{ number_format($bb->revenue, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-3">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-bold text-gray-900">Best Selling Categories</h2>
            <span class="text-xs text-gray-400">By revenue</span>
        </div>
        @if($bestCategories->isNotEmpty())
            @php $bcMax = $bestCategories->max('total_revenue'); @endphp
            <div class="space-y-3">
                @foreach($bestCategories as $bc)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-gray-700">{{ $bc['name'] }}</span>
                            <span class="font-semibold text-gray-900">${{ number_format($bc['total_revenue'], 0) }} ({{ $bc['total_qty'] }})</span>
                        </div>
                        <div class="bg-gray-100 rounded-full h-2.5 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-orange-400 to-pink-500 rounded-full transition-all duration-1000" style="width: {{ $bcMax > 0 ? ($bc['total_revenue'] / $bcMax) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-center py-8">No data yet.</p>
        @endif
    </div>
    <div class="bg-white rounded-xl shadow-md p-6 animate-fade-in-up animate-delay-3">
        <h2 class="font-bold text-gray-900 mb-4">Product Performance (Stock vs Sales)</h2>
        <div class="overflow-y-auto max-h-80">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 uppercase text-xs">
                        <th class="pb-2">Product</th>
                        <th class="pb-2 text-right">Stock</th>
                        <th class="pb-2 text-right">Sold</th>
                        <th class="pb-2 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productPerformance as $pp)
                        <tr class="border-t border-gray-50">
                            <td class="py-2 text-gray-800 truncate max-w-[160px]">{{ $pp['name'] }}</td>
                            <td class="py-2 text-right {{ $pp['stock'] <= 5 ? 'text-amber-600 font-bold' : 'text-gray-600' }}">{{ $pp['stock'] }}</td>
                            <td class="py-2 text-right font-medium text-gray-800">{{ $pp['sold'] }}</td>
                            <td class="py-2 text-right text-gray-600">${{ number_format($pp['revenue'], 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-gray-400">No data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const colors = ['#6366f1','#8b5cf6','#ec4899','#f43f5e','#f97316','#eab308','#22c55e','#14b8a6','#06b6d4','#3b82f6'];

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: {!! $revenueLabels->toJson() !!},
            datasets: [{
                label: 'Revenue',
                data: {!! $revenueValues->toJson() !!},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#6366f1',
            }]
        },
        options: {
            responsive: true,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) { return '$' + Number(ctx.raw).toLocaleString(); }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(v) { return '$' + v.toLocaleString(); } },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: {!! $catLabels->toJson() !!},
            datasets: [{
                data: {!! $catValues->toJson() !!},
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = ((ctx.raw / total) * 100).toFixed(1);
                            return ctx.label + ': $' + Number(ctx.raw).toLocaleString() + ' (' + pct + '%)';
                        }
                    }
                }
            },
            cutout: '60%',
        }
    });

    new Chart(document.getElementById('topProductsChart'), {
        type: 'bar',
        data: {
            labels: {!! $topProducts->pluck('product_name')->toJson() !!},
            datasets: [{
                label: 'Qty Sold',
                data: {!! $topProducts->pluck('total_qty')->toJson() !!},
                backgroundColor: colors.slice(0, {{ min(10, $topProducts->count()) }}),
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterLabel: function(ctx) {
                            const revenues = {!! $topProducts->pluck('total_revenue')->toJson() !!};
                            return 'Revenue: $' + Number(revenues[ctx.dataIndex]).toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                y: { grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('hourChart'), {
        type: 'bar',
        data: {
            labels: {!! $hourLabels->map(fn($h) => $h.':00')->toJson() !!},
            datasets: [{
                label: 'Revenue',
                data: {!! $hourValues->toJson() !!},
                backgroundColor: '#8b5cf6',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const counts = {!! $hourCounts->toJson() !!};
                            return '$' + Number(ctx.raw).toLocaleString() + ' (' + counts[ctx.dataIndex] + ' orders)';
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(v) { return '$' + v.toLocaleString(); } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false }, ticks: { maxRotation: 45 } }
            }
        }
    });

    new Chart(document.getElementById('dayChart'), {
        type: 'bar',
        data: {
            labels: {!! $dayLabels->toJson() !!},
            datasets: [{
                label: 'Revenue',
                data: {!! $dayValues->toJson() !!},
                backgroundColor: '#f97316',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const counts = {!! $dayCounts->toJson() !!};
                            return '$' + Number(ctx.raw).toLocaleString() + ' (' + counts[ctx.dataIndex] + ' orders)';
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(v) { return '$' + v.toLocaleString(); } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    new Chart(document.getElementById('paymentChart'), {
        type: 'polarArea',
        data: {
            labels: {!! $paymentMethods->pluck('payment_method')->map(fn($m) => ucfirst(str_replace('_', ' ', $m ?? 'Unknown')))->toJson() !!},
            datasets: [{
                data: {!! $paymentMethods->pluck('total')->toJson() !!},
                backgroundColor: colors.slice(0, {{ max(1, $paymentMethods->count()) }}),
                borderWidth: 1,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const counts = {!! $paymentMethods->pluck('count')->toJson() !!};
                            return ctx.label + ': $' + Number(ctx.raw).toLocaleString() + ' (' + counts[ctx.dataIndex] + ' orders)';
                        }
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! $statusDistribution->pluck('status')->toJson() !!},
            datasets: [{
                data: {!! $statusDistribution->pluck('count')->toJson() !!},
                backgroundColor: ['#22c55e', '#3b82f6', '#f97316', '#6366f1', '#ef4444'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = ((ctx.raw / total) * 100).toFixed(1);
                            return ctx.label + ': ' + ctx.raw + ' (' + pct + '%)';
                        }
                    }
                }
            },
            cutout: '55%',
        }
    });

    @if($selectedProduct && $productStatusBreakdown->isNotEmpty())
    new Chart(document.getElementById('productStatusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! $productStatusBreakdown->pluck('status')->toJson() !!},
            datasets: [{
                data: {!! $productStatusBreakdown->pluck('qty')->toJson() !!},
                backgroundColor: ['#22c55e', '#3b82f6', '#f97316', '#6366f1', '#ef4444'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const revenues = {!! $productStatusBreakdown->pluck('revenue')->toJson() !!};
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = ((ctx.raw / total) * 100).toFixed(1);
                            return ctx.label + ': ' + ctx.raw + ' sold ($' + Number(revenues[ctx.dataIndex]).toLocaleString() + ', ' + pct + '%)';
                        }
                    }
                }
            },
            cutout: '55%',
        }
    });
    @endif
});
</script>
@endsection
