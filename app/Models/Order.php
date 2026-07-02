<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
class Order extends Model
{
    protected $fillable = ['user_id', 'order_number', 'status', 'total', 'shipping_address', 'payment_method', 'payment_status', 'paid_at', 'transaction_id', 'notes'];

    protected $casts = ['paid_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeCompleted($q)
    {
        return $q->whereIn('status', ['delivered', 'shipped']);
    }
    public function scopePaid($q)
    {
        return $q->where('payment_status', 'paid');
    }
    public function scopeRevenue($q)
    {
        return $q->where('status', '!=', 'cancelled');
    }
    public function scopePeriod($q, $start, $end)
    {
        return $q->whereBetween('created_at', [$start, $end]);
    }

    public static function revenueByPeriod($start, $end)
    {
        return self::revenue()->period($start, $end)->sum('total');
    }
    public static function ordersByPeriod($start, $end)
    {
        return self::period($start, $end)->count();
    }
    public static function topProducts($limit = 10, $start = null, $end = null)
    {
        $q = OrderItem::select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(price * quantity) as total_revenue'))
            ->groupBy('product_name')->orderByDesc('total_qty');
        if ($start && $end) {
            $q->whereHas('order', fn($o) => $o->period($start, $end));
        }
        return $q->take($limit)->get();
    }
    public static function revenueByCategory($start = null, $end = null)
    {
        $q = OrderItem::select('products.category_id', DB::raw('SUM(order_items.price * order_items.quantity) as total'))
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->groupBy('products.category_id');
        if ($start && $end) {
            $q->whereHas('order', fn($o) => $o->period($start, $end));
        }
        return $q->get();
    }
    public static function dailyRevenue($days = 30)
    {
        return self::revenue()->where('created_at', '>=', now()->subDays($days))
            ->select(DB::raw("DATE(created_at) as date"), DB::raw("SUM(total) as total"), DB::raw("COUNT(*) as count"))
            ->groupBy('date')->orderBy('date')->get();
    }
    public static function monthlyRevenue($months = 12)
    {
        return self::revenue()->where('created_at', '>=', now()->subMonths($months))
            ->select(DB::raw("strftime('%Y-%m', created_at) as month"), DB::raw("SUM(total) as total"), DB::raw("COUNT(*) as count"))
            ->groupBy('month')->orderBy('month')->get();
    }
    public static function weeklyRevenue($weeks = 12)
    {
        return self::revenue()->where('created_at', '>=', now()->subWeeks($weeks))
            ->select(DB::raw("strftime('%Y-%W', created_at) as week"), DB::raw("SUM(total) as total"), DB::raw("COUNT(*) as count"))
            ->groupBy('week')->orderBy('week')->get();
    }
    public static function yearlyRevenue($years = 5)
    {
        return self::revenue()->where('created_at', '>=', now()->subYears($years))
            ->select(DB::raw("strftime('%Y', created_at) as year"), DB::raw("SUM(total) as total"), DB::raw("COUNT(*) as count"))
            ->groupBy('year')->orderBy('year')->get();
    }
    public static function stockSummary()
    {
        return [
            'total' => Product::sum('stock'),
            'low_stock' => Product::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
            'out_of_stock' => Product::where('stock', 0)->count(),
            'total_value' => Product::select(DB::raw('SUM(price * stock) as value'))->value('value') ?? 0,
        ];
    }
    public static function topCustomers($limit = 10)
    {
        return self::select('user_id', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total) as total_spent'))
            ->revenue()->groupBy('user_id')->orderByDesc('total_spent')->with('user')->take($limit)->get();
    }
}
