<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\BulkUploadController;
use App\Http\Controllers\Admin\BiController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\UserOrderController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\ProductPunchController;
use App\Http\Controllers\Admin\ProductOrderController;
use App\Http\Controllers\MauticController;
use App\Http\Controllers\ErpNextController;
use App\Http\Controllers\AiAgentController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\SSLCommerzController;
use App\Http\Controllers\Admin\AutomationHubController;
use App\Http\Controllers\Admin\ServiceHealthController;
use App\Http\Controllers\Admin\SystemStatusController;
use App\Http\Controllers\Admin\BackupRestoreController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/category/{category}', [ProductController::class, 'category'])->name('products.category');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cart}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');

Route::get('/payment/{order}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/payment/{order}', [PaymentController::class, 'process'])->name('payment.process');

// SSLCommerz Callbacks
Route::post('/sslcommerz/success/{order}', [SSLCommerzController::class, 'success'])->name('sslcommerz.success');
Route::post('/sslcommerz/fail/{order}', [SSLCommerzController::class, 'fail'])->name('sslcommerz.fail');
Route::post('/sslcommerz/cancel/{order}', [SSLCommerzController::class, 'cancel'])->name('sslcommerz.cancel');
Route::post('/sslcommerz/ipn/{order}', [SSLCommerzController::class, 'ipn'])->name('sslcommerz.ipn');

Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('products.reviews.store')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/my-orders', [UserOrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{order}', [UserOrderController::class, 'show'])->name('orders.show');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/automation-hub', [AutomationHubController::class, 'index'])->name('automation-hub');
    Route::get('/service-health', [ServiceHealthController::class, 'index'])->name('service-health');
    Route::get('/service-health/check', [ServiceHealthController::class, 'checkAll'])->name('service-health.check');
    Route::get('/service-health/test/{service}', [ServiceHealthController::class, 'testService'])->name('service-health.test');
    Route::get('/system-status', [SystemStatusController::class, 'index'])->name('system-status');
    Route::get('/system-status/api', [SystemStatusController::class, 'api'])->name('system-status.api');
    Route::post('/system-status/promotion', [SystemStatusController::class, 'createPromotion'])->name('system-status.create-promo');
    Route::resource('categories', AdminCategoryController::class)->except('show');
    Route::patch('/categories/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::resource('products', AdminProductController::class)->except('show');
    Route::patch('/products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::post('/inventory/{product}', [InventoryController::class, 'store'])->name('inventory.store');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/bi', [BiController::class, 'index'])->name('bi.index');

    Route::get('/export/orders', [ExportController::class, 'orders'])->name('export.orders');
    Route::get('/export/products', [ExportController::class, 'products'])->name('export.products');
    Route::get('/export/categories', [ExportController::class, 'categories'])->name('export.categories');
    Route::get('/export/inventory', [ExportController::class, 'inventory'])->name('export.inventory');
    Route::get('/export/analytics', [ExportController::class, 'analytics'])->name('export.analytics');

    Route::get('/bulk-upload', [BulkUploadController::class, 'index'])->name('bulkupload.index');
    Route::post('/bulk-upload/upload', [BulkUploadController::class, 'upload'])->name('bulkupload.upload');
    Route::post('/bulk-upload/sync', [BulkUploadController::class, 'sync'])->name('bulkupload.sync');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Chat routes (admin)
    Route::get('/chat', [AdminChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{session}', [AdminChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{session}/send', [AdminChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/{session}/poll', [AdminChatController::class, 'poll'])->name('chat.poll');
    Route::post('/chat/{session}/close', [AdminChatController::class, 'close'])->name('chat.close');
    Route::post('/chat/{session}/reopen', [AdminChatController::class, 'reopen'])->name('chat.reopen');
    Route::get('/chat/unread/count', [AdminChatController::class, 'unreadCount'])->name('chat.unread');

    // Product Punches
    Route::get('/product-punches', [ProductPunchController::class, 'index'])->name('product-punches.index');
    Route::get('/product-punches/create', [ProductPunchController::class, 'create'])->name('product-punches.create');
    Route::post('/product-punches', [ProductPunchController::class, 'store'])->name('product-punches.store');
    Route::get('/product-punches/{productPunch}/edit', [ProductPunchController::class, 'edit'])->name('product-punches.edit');
    Route::put('/product-punches/{productPunch}', [ProductPunchController::class, 'update'])->name('product-punches.update');
    Route::delete('/product-punches/{productPunch}', [ProductPunchController::class, 'destroy'])->name('product-punches.destroy');
    Route::get('/product-punches/export', [ProductPunchController::class, 'export'])->name('product-punches.export');

    // Product Orders
    Route::get('/product-orders', [ProductOrderController::class, 'index'])->name('product-orders.index');
    Route::get('/product-orders/create', [ProductOrderController::class, 'create'])->name('product-orders.create');
    Route::post('/product-orders', [ProductOrderController::class, 'store'])->name('product-orders.store');
    Route::get('/product-orders/{productOrder}/edit', [ProductOrderController::class, 'edit'])->name('product-orders.edit');
    Route::put('/product-orders/{productOrder}', [ProductOrderController::class, 'update'])->name('product-orders.update');
    Route::delete('/product-orders/{productOrder}', [ProductOrderController::class, 'destroy'])->name('product-orders.destroy');
    Route::post('/product-orders/{productOrder}/send-mail', [ProductOrderController::class, 'sendMail'])->name('product-orders.mail');
    Route::patch('/product-orders/{productOrder}/status', [ProductOrderController::class, 'updateStatus'])->name('product-orders.status');
    Route::get('/product-orders/export', [ProductOrderController::class, 'export'])->name('product-orders.export');

    // User Management
    Route::get('/users', [App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserManagementController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/role', [App\Http\Controllers\Admin\UserManagementController::class, 'updateRole'])->name('users.role');
    Route::get('/permissions', [App\Http\Controllers\Admin\UserManagementController::class, 'permissions'])->name('users.permissions');
    Route::post('/permissions', [App\Http\Controllers\Admin\UserManagementController::class, 'permissions'])->name('users.permissions.store');
});

Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Chat routes (visitors)
Route::post('/chat/session', [ChatController::class, 'session'])->name('chat.session');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
Route::get('/chat/poll', [ChatController::class, 'poll'])->name('chat.poll');

// Mautic Marketing Automation Routes
Route::post('/mautic/webhook', [MauticController::class, 'webhook'])->name('mautic.webhook');
Route::post('/mautic/track/page', [MauticController::class, 'trackPage'])->name('mautic.track.page');
Route::post('/mautic/track/product', [MauticController::class, 'trackProductView'])->name('mautic.track.product');
Route::post('/mautic/track/cart-abandonment', [MauticController::class, 'trackCartAbandonment'])->name('mautic.track.cart-abandonment');

// Mautic Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/mautic', [MauticController::class, 'index'])->name('mautic.index');
    Route::get('/mautic/test', [MauticController::class, 'testConnection'])->name('mautic.test');
    Route::get('/mautic/contacts', [MauticController::class, 'getContacts'])->name('mautic.contacts');
    Route::post('/mautic/sync', [MauticController::class, 'syncContact'])->name('mautic.sync');
});

// ERPNext Integration Routes
Route::post('/erpnext/webhook', [ErpNextController::class, 'webhook'])->name('erpnext.webhook');

// ERPNext Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/erpnext', [ErpNextController::class, 'index'])->name('erpnext.index');
    Route::get('/erpnext/test', [ErpNextController::class, 'testConnection'])->name('erpnext.test');
    Route::post('/erpnext/sync/products', [ErpNextController::class, 'syncProducts'])->name('erpnext.sync.products');
    Route::post('/erpnext/sync/product/{product}', [ErpNextController::class, 'syncProduct'])->name('erpnext.sync.product');
    Route::post('/erpnext/sync/customers', [ErpNextController::class, 'syncCustomers'])->name('erpnext.sync.customers');
    Route::post('/erpnext/sync/orders', [ErpNextController::class, 'syncOrders'])->name('erpnext.sync.orders');
    Route::post('/erpnext/sync/inventory', [ErpNextController::class, 'syncInventory'])->name('erpnext.sync.inventory');
    Route::get('/erpnext/items', [ErpNextController::class, 'getItems'])->name('erpnext.items');
    Route::get('/erpnext/customers', [ErpNextController::class, 'getCustomers'])->name('erpnext.customers');
    Route::get('/erpnext/orders', [ErpNextController::class, 'getSalesOrders'])->name('erpnext.orders');
    Route::get('/erpnext/warehouses', [ErpNextController::class, 'getWarehouses'])->name('erpnext.warehouses');
    Route::get('/erpnext/stock', [ErpNextController::class, 'getStockBalance'])->name('erpnext.stock');
});

// AI Agent Routes
Route::post('/api/ai/chat', [AiAgentController::class, 'chat'])->name('ai.chat');
Route::post('/api/botframework/messages', [AiAgentController::class, 'botframeworkWebhook'])->name('ai.botframework.webhook');

// Self-hosted Analytics Tracking
use App\Models\PageView;
Route::post('/api/track/page-view', function (\Illuminate\Http\Request $request) {
    try {
        PageView::create([
            'url' => $request->input('url'),
            'path' => $request->input('path'),
            'referrer' => $request->input('referrer'),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ]);
    } catch (\Throwable $e) {
        // silently fail
    }
    return response()->noContent();
})->middleware('throttle:60,1');

// AI Agent Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/ai-agents', [AiAgentController::class, 'index'])->name('ai-agents.index');
    Route::get('/ai-agents/status', [AiAgentController::class, 'status'])->name('ai-agents.status');
    Route::get('/ai-agents/test', [AiAgentController::class, 'testConnection'])->name('ai-agents.test');
    Route::get('/ai-agents/provider', [AiAgentController::class, 'getProvider'])->name('ai-agents.provider');
    Route::post('/ai-agents/provider', [AiAgentController::class, 'setProvider'])->name('ai-agents.provider.set');
    Route::post('/ai-agents/train', [AiAgentController::class, 'trainRasa'])->name('ai-agents.train');
});

// Recommendation Engine Routes
Route::get('/api/recommendations', [RecommendationController::class, 'getRecommendations'])->name('recommendations.get');
Route::get('/api/recommendations/user/{userId}', [RecommendationController::class, 'forUser'])->name('recommendations.for-user');
Route::get('/api/recommendations/similar/{productId}', [RecommendationController::class, 'similar'])->name('recommendations.similar');
Route::get('/api/recommendations/popular', [RecommendationController::class, 'popular'])->name('recommendations.popular');
Route::get('/api/recommendations/trending', [RecommendationController::class, 'trending'])->name('recommendations.trending');
Route::get('/api/recommendations/product/{productId}', [RecommendationController::class, 'productWidget'])->name('recommendations.product-widget');
Route::post('/api/recommendations/rate', [RecommendationController::class, 'addRating'])->name('recommendations.rate');

// Recommendation Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');
    Route::get('/recommendations/test', [RecommendationController::class, 'testConnection'])->name('recommendations.test');
    Route::get('/recommendations/model', [RecommendationController::class, 'modelInfo'])->name('recommendations.model');
    Route::post('/recommendations/train', [RecommendationController::class, 'trainModel'])->name('recommendations.train');
    Route::post('/recommendations/sync', [RecommendationController::class, 'syncRatings'])->name('recommendations.sync');
});

// Backup & Restore Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/backup-restore', [BackupRestoreController::class, 'index'])->name('backup-restore');
    Route::post('/backup-restore/backup', [BackupRestoreController::class, 'backup'])->name('backup.create');
    Route::post('/backup-restore/backup-full', [BackupRestoreController::class, 'backupFull'])->name('backup.create-full');
    Route::get('/backup-restore/download/{filename}', [BackupRestoreController::class, 'download'])->name('backup.download');
    Route::post('/backup-restore/restore', [BackupRestoreController::class, 'restore'])->name('backup.restore');
    Route::delete('/backup-restore/delete/{filename}', [BackupRestoreController::class, 'destroy'])->name('backup.delete');

    // Payment Methods
    Route::get('/payment-methods', [App\Http\Controllers\Admin\PaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::get('/payment-methods/{paymentMethod}/edit', [App\Http\Controllers\Admin\PaymentMethodController::class, 'edit'])->name('payment-methods.edit');
    Route::put('/payment-methods/{paymentMethod}', [App\Http\Controllers\Admin\PaymentMethodController::class, 'update'])->name('payment-methods.update');

    // Payments
    Route::get('/payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('payments.show');
    Route::post('/payments/{payment}/verify', [App\Http\Controllers\Admin\PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [App\Http\Controllers\Admin\PaymentController::class, 'reject'])->name('payments.reject');
});
