<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    protected $fillable = ['url', 'path', 'referrer', 'user_agent', 'ip'];

    public function scopePeriod($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public static function dailyViews($days = 30)
    {
        return self::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public static function monthlyViews($months = 12)
    {
        return self::where('created_at', '>=', now()->subMonths($months))
            ->selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public static function topPages($limit = 10)
    {
        return self::selectRaw('path, url, COUNT(*) as count')
            ->groupBy('path', 'url')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }

    public static function topReferrers($limit = 10)
    {
        return self::whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->selectRaw('referrer, COUNT(*) as count')
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }
}
