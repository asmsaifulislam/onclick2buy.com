<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BiController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->period ?? 'monthly';
        $categoryId = $request->category;
        $productId = $request->product;
        $statusFilter = $request->status;
        $buyerId = $request->buyer;

        $range = match ($period) {
            'daily' => ['start' => now()->subDays(30), 'end' => now(), 'label' => 'Last 30 Days'],
            'weekly' => ['start' => now()->subWeeks(12), 'end' => now(), 'label' => 'Last 12 Weeks'],
            'yearly' => ['start' => now()->subYears(5), 'end' => now(), 'label' => 'Last 5 Years'],
            default => ['start' => now()->subMonths(12), 'end' => now(), 'label' => 'Last 12 Months'],
        };
        $range['period'] = $period;

        $start = $range['start'];
        $end = $range['end'];

        // Build base order query with filters
        $orderQuery = Order::revenue()->period($start, $end);
        if ($statusFilter) {
            $orderQuery->where('status', $statusFilter);
        }
        if ($buyerId) {
            $orderQuery->where('user_id', $buyerId);
        }
        if ($productId) {
            $orderQuery->whereHas('items', fn($q) => $q->where('product_id', $productId));
        }

        // Order item query with filters
        $itemQuery = OrderItem::whereHas('order', function ($q) use ($start, $end, $statusFilter, $buyerId, $productId) {
            $q->revenue()->period($start, $end);
            if ($statusFilter) $q->where('status', $statusFilter);
            if ($buyerId) $q->where('user_id', $buyerId);
            if ($productId) $q->whereHas('items', fn($i) => $i->where('product_id', $productId));
        });
        if ($productId) {
            $itemQuery->where('product_id', $productId);
        }

        // KPIs
        $totalRevenue = (clone $orderQuery)->sum('total');
        $totalOrders = (clone $orderQuery)->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $totalProducts = Product::count();
        $pendingOrders = Order::where('status', 'pending')->when($buyerId, fn($q) => $q->where('user_id', $buyerId))->count();
        $cancelledOrders = (clone $orderQuery)->where('status', 'cancelled')->count();
        $totalCustomers = User::where('is_admin', false)->count();

        // Revenue trend (filtered)
        $dateExpr = match ($period) {
            'daily' => DB::raw("DATE(created_at) as label"),
            'weekly' => DB::raw("strftime('%Y-%W', created_at) as label"),
            'yearly' => DB::raw("strftime('%Y', created_at) as label"),
            default => DB::raw("strftime('%Y-%m', created_at) as label"),
        };
        $revenueData = (clone $orderQuery)
            ->select($dateExpr, DB::raw("SUM(total) as total"), DB::raw("COUNT(*) as count"))
            ->groupBy('label')->orderBy('label')->get();
        $revenueLabels = $revenueData->pluck('label');
        $revenueValues = $revenueData->pluck('total');
        $revenueCounts = $revenueData->pluck('count');

        // Category revenue
        $categoryRevenue = (clone $itemQuery)
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->select('products.category_id', DB::raw('SUM(order_items.price * order_items.quantity) as total'))
            ->groupBy('products.category_id')->get();
        $catLabels = $categoryRevenue->pluck('category_id')->map(fn($id) => Category::find($id)?->name ?? 'Uncategorized');
        $catValues = $categoryRevenue->pluck('total');

        // Top products
        $topProducts = (clone $itemQuery)
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(price * quantity) as total_revenue'))
            ->groupBy('product_name', 'product_id')->orderByDesc('total_qty')->take(10)->get();

        // Sales by hour
        $salesByHour = (clone $orderQuery)
            ->select(DB::raw("strftime('%H', created_at) as hour"), DB::raw("SUM(total) as total"), DB::raw("COUNT(*) as count"))
            ->groupBy('hour')->orderBy('hour')->get();
        $hourLabels = $salesByHour->pluck('hour');
        $hourValues = $salesByHour->pluck('total');
        $hourCounts = $salesByHour->pluck('count');

        // Sales by day of week
        $salesByDay = (clone $orderQuery)
            ->select(DB::raw("strftime('%w', created_at) as day"), DB::raw("SUM(total) as total"), DB::raw("COUNT(*) as count"))
            ->groupBy('day')->orderBy('day')->get();
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $dayLabels = $salesByDay->pluck('day')->map(fn($d) => $dayNames[(int)$d] ?? 'Unknown');
        $dayValues = $salesByDay->pluck('total');
        $dayCounts = $salesByDay->pluck('count');

        // Payment method distribution
        $paymentMethods = (clone $orderQuery)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')->get();

        // Order status distribution
        $statusDistribution = (clone $orderQuery)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')->get();

        // Status breakdown for selected product (if product is selected)
        $productStatusBreakdown = collect();
        $selectedProduct = null;
        if ($productId) {
            $selectedProduct = Product::find($productId);
            $productStatusBreakdown = OrderItem::where('product_id', $productId)
                ->whereHas('order', function ($q) use ($start, $end, $buyerId) {
                    $q->period($start, $end);
                    if ($buyerId) $q->where('user_id', $buyerId);
                })
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->select('orders.status', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.price * order_items.quantity) as revenue'))
                ->groupBy('orders.status')->get();
        }

        // Buyer breakdown for selected product
        $buyerBreakdown = collect();
        if ($productId) {
            $buyerBreakdown = OrderItem::where('product_id', $productId)
                ->whereHas('order', fn($q) => $q->period($start, $end))
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('users', 'users.id', '=', 'orders.user_id')
                ->select('users.name', 'users.email', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.price * order_items.quantity) as revenue'))
                ->groupBy('users.id', 'users.name', 'users.email')->orderByDesc('revenue')->get();
        }

        // Best selling categories ("companies")
        $bestCategoriesQuery = OrderItem::select('products.category_id', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'))
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereHas('order', fn($o) => $o->revenue()->period($start, $end))
            ->groupBy('products.category_id')->orderByDesc('total_revenue');
        if ($statusFilter) {
            $bestCategoriesQuery->whereHas('order', fn($o) => $o->where('status', $statusFilter));
        }
        if ($buyerId) {
            $bestCategoriesQuery->whereHas('order', fn($o) => $o->where('user_id', $buyerId));
        }
        $bestCategories = $bestCategoriesQuery->get()->map(fn($item) => [
            'name' => Category::find($item->category_id)?->name ?? 'Uncategorized',
            'total_qty' => $item->total_qty,
            'total_revenue' => $item->total_revenue,
        ]);

        // Growth
        $currentPeriodTotal = $totalRevenue;
        $prevEnd = $start->copy();
        $prevStart = $start->copy()->subDays($start->diffInDays($end) + 1);
        $previousQuery = Order::revenue()->period($prevStart, $prevEnd);
        if ($statusFilter) $previousQuery->where('status', $statusFilter);
        if ($buyerId) $previousQuery->where('user_id', $buyerId);
        if ($productId) $previousQuery->whereHas('items', fn($q) => $q->where('product_id', $productId));
        $previousRevenue = (clone $previousQuery)->sum('total');
        $growthPercent = $previousRevenue > 0 ? round(($currentPeriodTotal - $previousRevenue) / $previousRevenue * 100, 1) : 0;

        // Product performance
        $productPerfQuery = Product::active()
            ->withCount(['orderItems as total_sold' => fn($q) => $q->whereHas('order', fn($o) => $o->revenue()->period($start, $end))]);
        if ($productId) $productPerfQuery->where('id', $productId);
        $productPerformance = $productPerfQuery->get()->map(fn($p) => [
            'name' => $p->name,
            'stock' => $p->stock,
            'sold' => (int) $p->total_sold,
            'revenue' => $p->orderItems()->whereHas('order', fn($o) => $o->revenue()->period($start, $end))
                ->select(DB::raw('SUM(price * quantity) as total'))->value('total') ?? 0,
        ])->sortByDesc('sold')->values();

        // Filter dropdown data
        $categories = Category::active()->get();
        $products = Product::active()->get();
        $buyers = User::where('is_admin', false)->get();
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        return view('admin.bi.index', compact(
            'period', 'categoryId', 'productId', 'statusFilter', 'buyerId',
            'totalRevenue', 'totalOrders', 'avgOrderValue', 'totalProducts',
            'pendingOrders', 'cancelledOrders', 'totalCustomers',
            'revenueLabels', 'revenueValues', 'revenueCounts',
            'catLabels', 'catValues',
            'topProducts',
            'hourLabels', 'hourValues', 'hourCounts',
            'dayLabels', 'dayValues', 'dayCounts',
            'paymentMethods', 'statusDistribution',
            'bestCategories',
            'growthPercent', 'previousRevenue',
            'productPerformance',
            'categories', 'products', 'buyers', 'statuses',
            'productStatusBreakdown', 'selectedProduct', 'buyerBreakdown',
            'range',
        ));
    }
}
