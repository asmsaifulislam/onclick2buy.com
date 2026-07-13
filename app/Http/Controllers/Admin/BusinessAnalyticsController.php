<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Review;
use App\Models\Payment;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class BusinessAnalyticsController extends Controller
{
    public function index()
    {
        $now = now();
        $today = $now->copy()->startOfDay();
        $thisMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();
        $thisYear = $now->copy()->startOfYear();

        // KPI Cards
        $totalRevenue = Order::revenue()->period($thisYear, $now)->sum('total');
        $monthRevenue = Order::revenue()->period($thisMonth, $now)->sum('total');
        $lastMonthRevenue = Order::revenue()->period($lastMonth, $lastMonthEnd)->sum('total');
        $totalOrders = Order::period($thisYear, $now)->count();
        $monthOrders = Order::period($thisMonth, $now)->count();
        $totalCustomers = User::where('is_admin', false)->count();
        $monthCustomers = User::where('is_admin', false)->where('created_at', '>=', $thisMonth)->count();
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // Revenue Trend (last 12 months)
        $revenueTrend = collect(DB::select('SELECT strftime("%Y-%m", created_at) as month, SUM(total) as revenue, COUNT(*) as orders FROM orders WHERE status != "cancelled" AND created_at >= date("now", "-12 months") GROUP BY month ORDER BY month ASC'));

        // Daily Revenue (last 30 days)
        $dailyRevenue = DB::select("SELECT DATE(created_at) as day, SUM(total) as revenue, COUNT(*) as orders FROM orders WHERE status != 'cancelled' AND created_at >= date('now', '-30 days') GROUP BY day ORDER BY day ASC");

        // Top Products
        $topProducts = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(price * quantity) as total_revenue'))
            ->whereHas('order', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->groupBy('product_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // Category Performance
        $categoryPerformance = OrderItem::select('products.category_id', DB::raw('SUM(order_items.price * order_items.quantity) as revenue'), DB::raw('SUM(order_items.quantity) as units_sold'))
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('products.category_id')
            ->orderByDesc('revenue')
            ->with('product.category')
            ->get()
            ->map(function ($item) {
                $cat = Category::find($item->category_id);
                $item->category_name = $cat ? $cat->name : 'Uncategorized';
                return $item;
            });

        // Order Status Breakdown
        $orderStatus = DB::select("SELECT status, COUNT(*) as count FROM orders GROUP BY status");

        // Payment Methods
        $paymentMethods = DB::select("SELECT payment_method, COUNT(*) as count, SUM(total) as revenue FROM orders WHERE status != 'cancelled' GROUP BY payment_method");

        // Low Stock Products
        $lowStock = Product::where('is_active', true)->where('stock', '<=', 5)->orderBy('stock')->limit(10)->get();

        // Top Customers
        $topCustomers = Order::select('user_id', DB::raw('SUM(total) as total_spent'), DB::raw('COUNT(*) as order_count'))
            ->where('status', '!=', 'cancelled')
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->with('user')
            ->get();

        // Reviews Summary
        $avgRating = Review::avg('rating') ?? 0;
        $totalReviews = Review::count();

        // Pending Payments
        $pendingPayments = Payment::pending()->count();
        $pendingPaymentAmount = Payment::pending()->sum('amount');

        // Business Insights (AI-powered suggestions)
        $insights = $this->generateInsights(
            $monthRevenue, $lastMonthRevenue, $monthOrders,
            $avgOrderValue, $topProducts, $lowStock, $orderStatus,
            $categoryPerformance, $revenueGrowth
        );

        return view('admin.business-analytics.index', compact(
            'totalRevenue', 'monthRevenue', 'lastMonthRevenue',
            'totalOrders', 'monthOrders', 'totalCustomers', 'monthCustomers',
            'avgOrderValue', 'revenueGrowth',
            'revenueTrend', 'dailyRevenue', 'topProducts',
            'categoryPerformance', 'orderStatus', 'paymentMethods',
            'lowStock', 'topCustomers', 'avgRating', 'totalReviews',
            'pendingPayments', 'pendingPaymentAmount', 'insights'
        ));
    }

    private function generateInsights($monthRevenue, $lastMonthRevenue, $monthOrders, $avgOrderValue, $topProducts, $lowStock, $orderStatus, $categoryPerformance, $revenueGrowth)
    {
        $insights = [];

        // Revenue insight
        if ($revenueGrowth > 0) {
            $insights[] = ['type' => 'success', 'icon' => 'trending-up', 'title' => 'Revenue Growing', 'text' => "Revenue is up {$revenueGrowth}% compared to last month. Keep up the momentum!"];
        } elseif ($revenueGrowth < 0) {
            $insights[] = ['type' => 'warning', 'icon' => 'trending-down', 'title' => 'Revenue Declining', 'text' => "Revenue dropped " . abs($revenueGrowth) . "% vs last month. Consider running promotions or reviewing pricing."];
        }

        // Low stock alert
        if ($lowStock->count() > 0) {
            $names = $lowStock->pluck('name')->take(3)->implode(', ');
            $insights[] = ['type' => 'danger', 'icon' => 'alert', 'title' => 'Low Stock Alert', 'text' => "{$lowStock->count()} products are running low on stock: {$names}. Restock soon to avoid lost sales."];
        }

        // Average order value
        if ($avgOrderValue < 50) {
            $insights[] = ['type' => 'info', 'icon' => 'dollar', 'title' => 'Increase Average Order Value', 'text' => "Average order value is \${$avgOrderValue}. Consider adding upsells, bundles, or a free shipping threshold above \${$avgOrderValue}."];
        }

        // Top product insight
        if ($topProducts->count() > 0) {
            $top = $topProducts->first();
            $insights[] = ['type' => 'success', 'icon' => 'star', 'title' => 'Best Seller', 'text' => "\"{$top->product_name}\" is your top seller with {$top->total_sold} units sold (\${$top->total_revenue} revenue). Consider featuring it prominently."];
        }

        // Cancelled orders
        $cancelled = collect($orderStatus)->firstWhere('status', 'cancelled');
        $total = collect($orderStatus)->sum('count');
        if ($cancelled && $total > 0) {
            $cancelRate = round(($cancelled->count / $total) * 100, 1);
            if ($cancelRate > 10) {
                $insights[] = ['type' => 'warning', 'icon' => 'x-circle', 'title' => 'High Cancellation Rate', 'text' => "{$cancelRate}% of orders are cancelled. Review order flow and customer expectations to reduce cancellations."];
            }
        }

        // Category insight
        if ($categoryPerformance->count() > 1) {
            $topCat = $categoryPerformance->first();
            $insights[] = ['type' => 'info', 'icon' => 'category', 'title' => 'Top Category', 'text' => "\"{$topCat->category_name}\" generates the most revenue (\${$topCat->revenue}). Consider expanding this category's product range."];
        }

        if (empty($insights)) {
            $insights[] = ['type' => 'info', 'icon' => 'info', 'title' => 'Getting Started', 'text' => 'As more orders come in, you will see business insights and suggestions here.'];
        }

        return $insights;
    }
}
