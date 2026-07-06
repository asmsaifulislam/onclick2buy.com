<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->period ?? 'monthly';
        $start = $request->start ? now()->parse($request->start) : now()->subYear();
        $end = $request->end ? now()->parse($request->end)->endOfDay() : now();

        $range = match($period) {
            'daily' => ['start' => now()->subDays(30), 'end' => now(), 'revenue' => Order::dailyRevenue(30), 'label' => 'Last 30 Days'],
            'weekly' => ['start' => now()->subWeeks(12), 'end' => now(), 'revenue' => Order::weeklyRevenue(12), 'label' => 'Last 12 Weeks'],
            'yearly' => ['start' => now()->subYears(5), 'end' => now(), 'revenue' => Order::yearlyRevenue(5), 'label' => 'Last 5 Years'],
            default => ['start' => now()->subMonths(12), 'end' => now(), 'revenue' => Order::monthlyRevenue(12), 'label' => 'Last 12 Months'],
        };
        $range['period'] = $period;

        $totalRevenue = Order::revenue()->period($range['start'], $range['end'])->sum('total');
        $totalOrders = Order::period($range['start'], $range['end'])->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $totalProducts = Product::count();
        $topProducts = Order::topProducts(10, $range['start'], $range['end']);
        $categoryRevenue = Order::revenueByCategory($range['start'], $range['end']);
        $stockSummary = Order::stockSummary();
        $topCustomers = Order::topCustomers(10);
        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::where('status', 'processing')->count();

        $revenueData = $range['revenue'];
        $chartLabels = $revenueData->pluck($period === 'daily' ? 'date' : ($period === 'weekly' ? 'week' : ($period === 'yearly' ? 'year' : 'month')));
        $chartValues = $revenueData->pluck('total');
        $chartCounts = $revenueData->pluck('count');

        $categoryLabels = $categoryRevenue->pluck('category_id')->map(fn($id) => Category::find($id)?->name ?? 'Uncategorized');
        $categoryValues = $categoryRevenue->pluck('total');

        $pageViews = PageView::period($range['start'], $range['end'])->count();
        $dailyViews = PageView::dailyViews(30);
        $topPages = PageView::topPages(10);
        $topReferrers = PageView::topReferrers(10);

        return view('admin.analytics.index', compact(
            'totalRevenue', 'totalOrders', 'avgOrderValue', 'totalProducts',
            'topProducts', 'categoryRevenue', 'stockSummary', 'topCustomers',
            'pendingOrders', 'processingOrders', 'range',
            'chartLabels', 'chartValues', 'chartCounts',
            'categoryLabels', 'categoryValues',
            'pageViews', 'dailyViews', 'topPages', 'topReferrers'
        ));
    }
}
