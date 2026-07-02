<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    public function orders(Request $request)
    {
        $orders = Order::with('user', 'items')->latest()->get();
        $csv = "Order #,Customer,Email,Total,Status,Payment,Items,Date\n";
        foreach ($orders as $o) {
            $items = $o->items->map(fn($i) => $i->product_name . ' x' . $i->quantity)->implode('; ');
            $csv .= "\"{$o->order_number}\",\"{$o->user->name}\",\"{$o->user->email}\",{$o->total},\"{$o->status}\",\"{$o->payment_method}\",\"{$items}\",\"{$o->created_at->format('Y-m-d')}\"\n";
        }
        return Response::make($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="orders.csv"']);
    }
    public function products()
    {
        $products = Product::with('category')->latest()->get();
        $csv = "Name,Category,Price,Sale Price,Stock,SKU,Status\n";
        foreach ($products as $p) {
            $csv .= "\"{$p->name}\",\"{$p->category?->name}\",{$p->price}," . ($p->sale_price ?? '') . ",{$p->stock},\"{$p->sku}\"," . ($p->is_active ? 'Active' : 'Inactive') . "\n";
        }
        return Response::make($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="products.csv"']);
    }
    public function categories()
    {
        $categories = Category::latest()->get();
        $csv = "Name,Slug,Status\n";
        foreach ($categories as $c) {
            $csv .= "\"{$c->name}\",\"{$c->slug}\"," . ($c->is_active ? 'Active' : 'Inactive') . "\n";
        }
        return Response::make($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="categories.csv"']);
    }
    public function inventory()
    {
        $products = Product::with('category')->latest()->get();
        $csv = "Product,Category,Current Stock,Status\n";
        foreach ($products as $p) {
            $status = $p->stock > 10 ? 'In Stock' : ($p->stock > 0 ? 'Low Stock' : 'Out of Stock');
            $csv .= "\"{$p->name}\",\"{$p->category?->name}\",{$p->stock},\"{$status}\"\n";
        }
        return Response::make($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="inventory.csv"']);
    }
    public function analytics(Request $request)
    {
        $period = $request->period ?? 'monthly';
        $rangeStart = match($period) { 'daily' => now()->subDays(30), 'weekly' => now()->subWeeks(12), 'yearly' => now()->subYears(5), default => now()->subMonths(12) };
        $csv = "Metric,Value\n";
        $csv .= "Total Revenue," . Order::revenue()->period($rangeStart, now())->sum('total') . "\n";
        $csv .= "Total Orders," . Order::period($rangeStart, now())->count() . "\n";
        $csv .= "Total Products," . Product::count() . "\n\n";
        $csv .= "Top Products\n";
        $csv .= "Product,Quantity Sold,Revenue\n";
        foreach (Order::topProducts(10, $rangeStart, now()) as $p) {
            $csv .= "\"{$p->product_name}\",{$p->total_qty},{$p->total_revenue}\n";
        }
        return Response::make($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="analytics.csv"']);
    }
}
