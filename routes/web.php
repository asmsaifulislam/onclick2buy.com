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

Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('products.reviews.store')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/my-orders', [UserOrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{order}', [UserOrderController::class, 'show'])->name('orders.show');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
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
