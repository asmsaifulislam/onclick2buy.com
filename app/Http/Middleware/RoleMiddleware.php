<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        $routeName = Route::currentRouteName();
        $pageKey = $this->mapRouteToPage($routeName);

        if ($pageKey && !$user->canAccessPage($pageKey)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }

    private function mapRouteToPage(?string $routeName): ?string
    {
        if (!$routeName) return null;

        $mapping = [
            'admin.dashboard' => 'dashboard',
            'admin.categories.*' => 'categories',
            'admin.products.*' => 'products',
            'admin.orders.*' => 'orders',
            'admin.inventory.*' => 'inventory',
            'admin.analytics.*' => 'analytics',
            'admin.bi.*' => 'bi',
            'admin.bulkupload.*' => 'bulkupload',
            'admin.settings.*' => 'settings',
            'admin.chat.*' => 'chat',
            'admin.product-punches.*' => 'product_punches',
            'admin.product-orders.*' => 'product_orders',
            'admin.users.*' => 'users',
        ];

        foreach ($mapping as $pattern => $page) {
            $pattern = str_replace('*', '.*', $pattern);
            if (preg_match('#^' . $pattern . '$#', $routeName)) {
                return $page;
            }
        }

        return null;
    }
}
