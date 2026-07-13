@extends('layouts.admin')
@section('title', 'Business Analytics')
@section('content')
<style>
.ba-card{background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.04);}
.ba-card h3{font-size:16px; font-weight:600; color:#111827; margin:0 0 16px 0;}
.ba-table{width:100%; border-collapse:collapse; font-size:13px;}
.ba-table th{text-align:left; padding:10px 8px; color:#6b7280; font-weight:600; font-size:11px; text-transform:uppercase; letter-spacing:0.05em; border-bottom:2px solid #e5e7eb;}
.ba-table td{padding:10px 8px; border-bottom:1px solid #f3f4f6; color:#374151;}
.ba-table tr:last-child td{border-bottom:none;}
.ba-kpi{background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:20px 24px; box-shadow:0 1px 3px rgba(0,0,0,0.04); position:relative; overflow:hidden;}
.ba-kpi::before{content:''; position:absolute; top:0; left:0; width:4px; height:100%; border-radius:4px 0 0 4px;}
.ba-kpi.kpi-revenue::before{background:#6366f1;}
.ba-kpi.kpi-month::before{background:#f59e0b;}
.ba-kpi.kpi-customers::before{background:#10b981;}
.ba-kpi.kpi-avg::before{background:#3b82f6;}
.ba-kpi.kpi-rating::before{background:#ec4899;}
.ba-kpi p.kpi-label{font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; margin:0 0 6px 0;}
.ba-kpi p.kpi-value{font-size:28px; font-weight:700; color:#111827; margin:0;}
.ba-kpi p.kpi-sub{font-size:12px; margin:6px 0 0 0;}
.ba-insight{background:rgba(255,255,255,0.12); border-radius:12px; padding:16px; backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.1);}
.ba-insight p.insight-title{font-weight:600; font-size:14px; margin:0 0 4px 0;}
.ba-insight p.insight-text{font-size:13px; opacity:0.9; margin:0; line-height:1.5;}
.ba-tag{display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;}
.ba-tag-green{background:#ecfdf5; color:#059669;}
.ba-tag-yellow{background:#fffbeb; color:#d97706;}
.ba-tag-red{background:#fef2f2; color:#dc2626;}
.ba-tag-blue{background:#eff6ff; color:#2563eb;}
.ba-progress{height:6px; background:#e5e7eb; border-radius:3px; overflow:hidden; margin-top:4px;}
.ba-progress-fill{height:100%; border-radius:3px; transition:width 0.5s;}
.ba-row{display:grid; gap:20px; margin-bottom:24px;}
.ba-row-2{grid-template-columns:2fr 1fr;}
.ba-row-3{grid-template-columns:1fr 1fr 1fr;}
.ba-row-5{grid-template-columns:repeat(5,1fr);}
.chart-wrap{position:relative; width:100%;}
.chart-wrap canvas{width:100% !important;}
@media(max-width:1200px){.ba-row-5{grid-template-columns:repeat(3,1fr);} .ba-row-3{grid-template-columns:1fr;}}
@media(max-width:768px){.ba-row-5{grid-template-columns:1fr 1fr;} .ba-row-2{grid-template-columns:1fr;}}
</style>

<div style="max-width:1400px; margin:0 auto; padding:0 4px;">
    {{-- Header --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:28px;">
        <div>
            <h1 style="font-size:26px; font-weight:700; color:#111827; margin:0;">Business Analytics</h1>
            <p style="color:#6b7280; margin:4px 0 0 0; font-size:14px;">Complete overview of your store performance & AI-powered insights</p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:12px; color:#9ca3af;">Last updated</div>
            <div style="font-size:14px; font-weight:500; color:#374151;">{{ now()->format('M d, Y h:i A') }}</div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="ba-row ba-row-5">
        <div class="ba-kpi kpi-revenue">
            <p class="kpi-label">Total Revenue (YTD)</p>
            <p class="kpi-value" style="color:#6366f1;">${{ number_format($totalRevenue, 2) }}</p>
            <p class="kpi-sub" style="color:{{ $revenueGrowth >= 0 ? '#059669' : '#dc2626' }};">
                {{ $revenueGrowth >= 0 ? '&#9650;' : '&#9660;' }} {{ abs($revenueGrowth) }}% vs last month
            </p>
        </div>
        <div class="ba-kpi kpi-month">
            <p class="kpi-label">This Month</p>
            <p class="kpi-value" style="color:#f59e0b;">${{ number_format($monthRevenue, 2) }}</p>
            <p class="kpi-sub" style="color:#6b7280;">{{ $monthOrders }} orders placed</p>
        </div>
        <div class="ba-kpi kpi-customers">
            <p class="kpi-label">Total Customers</p>
            <p class="kpi-value" style="color:#10b981;">{{ number_format($totalCustomers) }}</p>
            <p class="kpi-sub" style="color:#10b981;">+{{ $monthCustomers }} new this month</p>
        </div>
        <div class="ba-kpi kpi-avg">
            <p class="kpi-label">Avg Order Value</p>
            <p class="kpi-value" style="color:#3b82f6;">${{ number_format($avgOrderValue, 2) }}</p>
            <p class="kpi-sub" style="color:#6b7280;">across {{ number_format($totalOrders) }} total orders</p>
        </div>
        <div class="ba-kpi kpi-rating">
            <p class="kpi-label">Customer Rating</p>
            <p class="kpi-value" style="color:#ec4899;">{{ number_format($avgRating, 1) }}<span style="font-size:16px; color:#d1d5db;"> / 5</span></p>
            <p class="kpi-sub" style="color:#6b7280;">{{ $totalReviews }} total reviews</p>
        </div>
    </div>

    {{-- AI Insights --}}
    <div style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 50%,#a855f7 100%); border-radius:16px; padding:28px; margin-bottom:24px; color:#fff; box-shadow:0 4px 15px rgba(99,102,241,0.3);">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px;">
            <div style="width:36px; height:36px; background:rgba(255,255,255,0.2); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                <svg style="width:20px; height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <h2 style="font-size:18px; font-weight:700; margin:0;">AI Business Insights & Suggestions</h2>
        </div>
        <div class="ba-row ba-row-2">
            @foreach($insights as $insight)
            <div class="ba-insight">
                <p class="insight-title">{{ $insight['title'] }}</p>
                <p class="insight-text">{{ $insight['text'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Charts Row 1 --}}
    <div class="ba-row ba-row-2" style="margin-bottom:24px;">
        <div class="ba-card">
            <h3>Revenue Trend <span style="font-size:12px; font-weight:400; color:#9ca3af;">(Last 12 Months)</span></h3>
            <div class="chart-wrap" style="height:320px;"><canvas id="revenueChart"></canvas></div>
        </div>
        <div class="ba-card">
            <h3>Order Status Distribution</h3>
            <div class="chart-wrap" style="height:320px;"><canvas id="statusChart"></canvas></div>
        </div>
    </div>

    {{-- Chart Row 2 --}}
    <div class="ba-card" style="margin-bottom:24px;">
        <h3>Daily Revenue <span style="font-size:12px; font-weight:400; color:#9ca3af;">(Last 30 Days)</span></h3>
        <div class="chart-wrap" style="height:260px;"><canvas id="dailyChart"></canvas></div>
    </div>

    {{-- Tables Row --}}
    <div class="ba-row ba-row-2" style="margin-bottom:24px;">
        {{-- Top Products --}}
        <div class="ba-card">
            <h3>Top Selling Products</h3>
            @if($topProducts->count() > 0)
            @php $maxRev = $topProducts->first()->total_revenue; @endphp
            <table class="ba-table">
                <thead><tr><th style="width:40px;">#</th><th>Product</th><th style="text-align:right;">Sold</th><th style="text-align:right;">Revenue</th><th style="width:120px;">Share</th></tr></thead>
                <tbody>
                @foreach($topProducts as $i => $p)
                @php $pct = $maxRev > 0 ? round(($p->total_revenue / $maxRev) * 100) : 0; @endphp
                <tr>
                    <td style="color:#9ca3af; font-weight:600;">{{ $i + 1 }}</td>
                    <td style="font-weight:500; color:#111827;">{{ $p->product_name }}</td>
                    <td style="text-align:right;">{{ $p->total_sold }}</td>
                    <td style="text-align:right; font-weight:600; color:#059669;">${{ number_format($p->total_revenue, 2) }}</td>
                    <td>
                        <div class="ba-progress"><div class="ba-progress-fill" style="width:{{ $pct }}%; background:linear-gradient(90deg,#6366f1,#a855f7);"></div></div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @else
            <p style="color:#9ca3af; text-align:center; padding:30px 0;">No sales data yet</p>
            @endif
        </div>

        {{-- Category Performance --}}
        <div class="ba-card">
            <h3>Category Performance</h3>
            <div class="chart-wrap" style="height:300px;"><canvas id="categoryChart"></canvas></div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="ba-row ba-row-3" style="margin-bottom:24px;">
        {{-- Top Customers --}}
        <div class="ba-card">
            <h3>Top Customers</h3>
            @forelse($topCustomers as $i => $tc)
            <div style="display:flex; align-items:center; gap:12px; padding:10px 0; {{ $i < $topCustomers->count() - 1 ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
                <div style="width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#6366f1,#a855f7); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:13px; flex-shrink:0;">
                    {{ strtoupper(substr($tc->user->name ?? 'U', 0, 1)) }}
                </div>
                <div style="flex:1; min-width:0;">
                    <p style="font-size:13px; font-weight:600; color:#111827; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $tc->user->name ?? 'Unknown' }}</p>
                    <p style="font-size:11px; color:#6b7280; margin:2px 0 0 0;">{{ $tc->order_count }} orders</p>
                </div>
                <div style="text-align:right;">
                    <p style="font-size:14px; font-weight:700; color:#059669; margin:0;">${{ number_format($tc->total_spent, 2) }}</p>
                </div>
            </div>
            @empty
            <p style="color:#9ca3af; text-align:center; padding:30px 0;">No customer data yet</p>
            @endforelse
        </div>

        {{-- Low Stock --}}
        <div class="ba-card">
            <h3 style="color:#dc2626;">Low Stock Alert</h3>
            @forelse($lowStock as $i => $ls)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; {{ $i < $lowStock->count() - 1 ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
                <div style="flex:1; min-width:0;">
                    <p style="font-size:13px; font-weight:500; color:#111827; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $ls->name }}</p>
                    <p style="font-size:11px; color:#6b7280; margin:2px 0 0 0;">SKU: {{ $ls->sku ?? 'N/A' }}</p>
                </div>
                @if($ls->stock == 0)
                <span class="ba-tag ba-tag-red">Out of Stock</span>
                @else
                <span class="ba-tag ba-tag-yellow">{{ $ls->stock }} left</span>
                @endif
            </div>
            @empty
            <div style="text-align:center; padding:30px 0;">
                <div style="width:48px; height:48px; background:#ecfdf5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
                    <svg style="width:24px; height:24px; color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p style="color:#059669; font-weight:500; font-size:14px;">All products well stocked</p>
            </div>
            @endforelse
        </div>

        {{-- Payment Methods --}}
        <div class="ba-card">
            <h3>Payment Methods</h3>
            @forelse($paymentMethods as $i => $pm)
            @php
                $totalPmRevenue = collect($paymentMethods)->sum('revenue');
                $pmPct = $totalPmRevenue > 0 ? round(($pm->revenue / $totalPmRevenue) * 100) : 0;
            @endphp
            <div style="padding:10px 0; {{ $i < count($paymentMethods) - 1 ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                    <p style="font-size:13px; font-weight:600; color:#111827; margin:0;">{{ $pm->payment_method ?? 'N/A' }}</p>
                    <p style="font-size:13px; font-weight:700; color:#111827; margin:0;">${{ number_format($pm->revenue, 2) }}</p>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <p style="font-size:11px; color:#6b7280; margin:0;">{{ $pm->count }} orders</p>
                    <p style="font-size:11px; color:#6b7280; margin:0;">{{ $pmPct }}%</p>
                </div>
                <div class="ba-progress" style="margin-top:6px;">
                    <div class="ba-progress-fill" style="width:{{ $pmPct }}%; background:linear-gradient(90deg,#3b82f6,#06b6d4);"></div>
                </div>
            </div>
            @empty
            <p style="color:#9ca3af; text-align:center; padding:30px 0;">No payment data yet</p>
            @endforelse
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const palette = ['#6366f1','#f59e0b','#10b981','#ef4444','#3b82f6','#8b5cf6','#ec4899','#14b8a6','#f97316','#06b6d4'];

Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = '#6b7280';

// Revenue Trend
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($revenueTrend->pluck('month')) !!},
        datasets: [{
            label: 'Revenue ($)',
            data: {!! json_encode($revenueTrend->pluck('revenue')->map(fn($v) => round($v, 2))) !!},
            backgroundColor: 'rgba(99,102,241,0.8)',
            hoverBackgroundColor: 'rgba(99,102,241,1)',
            borderRadius: 8,
            borderSkipped: false,
            order: 2,
        },{
            label: 'Orders',
            data: {!! json_encode($revenueTrend->pluck('orders')) !!},
            type: 'line',
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245,158,11,0.1)',
            pointBackgroundColor: '#f59e0b',
            pointRadius: 4,
            pointHoverRadius: 6,
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            yAxisID: 'y1',
            order: 1,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '$' + v.toLocaleString() } },
            y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});

// Order Status Doughnut
const statusData = {!! json_encode($orderStatus) !!};
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusData.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1)),
        datasets: [{ data: statusData.map(s => s.count), backgroundColor: ['#f59e0b','#3b82f6','#8b5cf6','#10b981','#ef4444','#6b7280','#ec4899'], borderWidth: 3, borderColor: '#fff', hoverOffset: 8 }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: { legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } } }
    }
});

// Daily Revenue
const dailyData = {!! json_encode($dailyRevenue) !!};
new Chart(document.getElementById('dailyChart'), {
    type: 'line',
    data: {
        labels: dailyData.map(d => d.day),
        datasets: [{
            label: 'Revenue',
            data: dailyData.map(d => d.revenue),
            borderColor: '#10b981',
            backgroundColor: (ctx) => {
                const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 260);
                g.addColorStop(0, 'rgba(16,185,129,0.2)');
                g.addColorStop(1, 'rgba(16,185,129,0.01)');
                return g;
            },
            fill: true,
            tension: 0.4,
            borderWidth: 2.5,
            pointRadius: 0,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: '#10b981',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '$' + v.toLocaleString() } }, x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } } }
    }
});

// Category Performance
const catData = {!! json_encode($categoryPerformance->toArray()) !!};
new Chart(document.getElementById('categoryChart'), {
    type: 'bar',
    data: {
        labels: catData.map(c => c.category_name),
        datasets: [{
            label: 'Revenue ($)',
            data: catData.map(c => c.revenue),
            backgroundColor: palette.slice(0, catData.length),
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => '$' + c.parsed.x.toLocaleString() } } },
        scales: { x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '$' + v.toLocaleString() } }, y: { grid: { display: false } } }
    }
});
</script>
@endsection
