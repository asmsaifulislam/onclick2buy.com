<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total');
        $recentOrders = Order::with('user')->latest()->take(5)->get();
        return view('admin.dashboard', compact('totalProducts', 'totalCategories', 'totalOrders', 'totalRevenue', 'recentOrders'));
    }
}
