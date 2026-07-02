<?php

$databasePath = __DIR__ . '/database/database.sqlite';

if (file_exists($databasePath)) {
    unlink($databasePath);
}
touch($databasePath);

$pdo = new PDO('sqlite:' . $databasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');

$pdo->exec("CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at DATETIME NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(255) NULL,
    remember_token VARCHAR(100) NULL,
    is_admin BOOLEAN DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
)");

$pdo->exec("CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
)");

$pdo->exec("CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id INTEGER NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INTEGER NOT NULL
)");

$pdo->exec("CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INTEGER NOT NULL
)");

$pdo->exec("CREATE TABLE cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
)");

$pdo->exec("CREATE TABLE jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
)");

$pdo->exec("CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options TEXT NULL,
    cancelled_at INTEGER NULL,
    created_at INTEGER NOT NULL,
    finished_at INTEGER NULL
)");

$pdo->exec("CREATE TABLE failed_jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid VARCHAR(255) UNIQUE NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("CREATE TABLE categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
)");

$pdo->exec("CREATE TABLE products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    stock INTEGER DEFAULT 0,
    sku VARCHAR(255) UNIQUE NULL,
    images JSON NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
)");

$pdo->exec("CREATE TABLE carts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NULL,
    session_id VARCHAR(255) NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)");

$pdo->exec("CREATE TABLE orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    order_number VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    total DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NULL,
    payment_method VARCHAR(100) NULL,
    payment_status VARCHAR(50) DEFAULT 'unpaid',
    paid_at TIMESTAMP NULL,
    transaction_id VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
)");

$pdo->exec("CREATE TABLE order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    product_id INTEGER NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INTEGER NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)");

$pdo->exec("CREATE TABLE reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    rating INTEGER NOT NULL,
    comment TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)");

$pdo->exec("CREATE TABLE inventory_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    user_id INTEGER NULL,
    type VARCHAR(50) NOT NULL,
    quantity INTEGER NOT NULL,
    previous_stock INTEGER NOT NULL,
    new_stock INTEGER NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
)");

$pdo->exec("CREATE TABLE store_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
)");

$pdo->exec("CREATE TABLE migrations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
)");

echo "All tables created successfully!\n";

echo "Seeding data...\n";

$now = date('Y-m-d H:i:s');

// Admin user
$adminPass = password_hash('password', PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO users (name, email, password, is_admin, created_at, updated_at) VALUES (?, ?, ?, 1, ?, ?)")
    ->execute(['Admin User', 'admin@admin.com', $adminPass, $now, $now]);

// Test user
$userPass = password_hash('password', PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO users (name, email, password, is_admin, created_at, updated_at) VALUES (?, ?, ?, 0, ?, ?)")
    ->execute(['Test User', 'user@user.com', $userPass, $now, $now]);

// Categories
$categories = [
    ['Electronics', 'electronics', 'Gadgets, devices, and tech accessories'],
    ['Clothing', 'clothing', 'Fashion apparel for men and women'],
    ['Home & Garden', 'home-garden', 'Furniture, decor, and gardening tools'],
    ['Sports & Outdoors', 'sports-outdoors', 'Sports equipment and outdoor gear'],
    ['Books', 'books', 'Fiction, non-fiction, and educational books'],
];

$catStmt = $pdo->prepare("INSERT INTO categories (name, slug, description, is_active, created_at, updated_at) VALUES (?, ?, ?, 1, ?, ?)");
foreach ($categories as $cat) {
    $catStmt->execute([$cat[0], $cat[1], $cat[2], $now, $now]);
}

// Products
$products = [
    [1, 'Wireless Bluetooth Headphones', 'wireless-bluetooth-headphones', 'Premium noise-cancelling wireless headphones with 30-hour battery life and crystal-clear sound quality.', 89.99, 69.99, 50, 'ELEC-BT-001'],
    [1, 'Smart Watch Pro', 'smart-watch-pro', 'Advanced smartwatch with heart rate monitor, GPS tracking, and 7-day battery life.', 199.99, null, 30, 'ELEC-SW-002'],
    [1, 'USB-C Hub 7-in-1', 'usb-c-hub-7-in-1', 'Multi-port USB-C hub with HDMI 4K, USB 3.0, SD card reader, and PD charging.', 34.99, 27.99, 100, 'ELEC-USB-003'],
    [2, 'Classic Denim Jacket', 'classic-denim-jacket', 'Timeless denim jacket made from premium cotton. Perfect for any casual occasion.', 59.99, 44.99, 40, 'CLTH-DJ-001'],
    [2, 'Cotton T-Shirt Pack (3-Pack)', 'cotton-t-shirt-pack', 'Soft, breathable 100% organic cotton t-shirts. Available in assorted colors.', 29.99, null, 80, 'CLTH-TS-002'],
    [2, 'Running Shoes Ultra', 'running-shoes-ultra', 'Lightweight running shoes with responsive cushioning and breathable mesh upper.', 119.99, 89.99, 25, 'CLTH-RS-003'],
    [3, 'Indoor Plant Pot Set', 'indoor-plant-pot-set', 'Set of 3 minimalist ceramic plant pots with drainage holes. Perfect for succulents.', 24.99, null, 60, 'HOME-PT-001'],
    [3, 'Stainless Steel Water Bottle', 'stainless-steel-water-bottle', 'Double-wall insulated water bottle. Keeps drinks cold 24h or hot 12h. 750ml.', 22.99, 18.99, 90, 'HOME-WB-002'],
    [3, 'LED Desk Lamp', 'led-desk-lamp', 'Adjustable LED desk lamp with 5 brightness levels, USB charging port, and clamp base.', 39.99, null, 45, 'HOME-LM-003'],
    [4, 'Yoga Mat Premium', 'yoga-mat-premium', 'Extra-thick non-slip yoga mat with carrying strap. 6mm thickness for maximum comfort.', 32.99, null, 35, 'SPRT-YM-001'],
    [4, 'Resistance Bands Set', 'resistance-bands-set', 'Set of 5 resistance bands with different tension levels. Includes door anchor and carry bag.', 19.99, 14.99, 70, 'SPRT-RB-002'],
    [4, 'Camping Tent 4-Person', 'camping-tent-4-person', 'Waterproof 4-person camping tent with easy setup. Includes rainfly and carry bag.', 149.99, 119.99, 15, 'SPRT-CT-003'],
    [5, 'The Art of Programming', 'art-of-programming', 'A comprehensive guide to software design patterns and clean code principles.', 42.99, null, 55, 'BOOK-PR-001'],
    [5, 'Mindful Living', 'mindful-living', 'Practical guide to mindfulness and meditation for a balanced modern life.', 15.99, 12.99, 40, 'BOOK-ML-002'],
    [5, 'World Atlas 2025', 'world-atlas-2025', 'Fully updated world atlas with detailed maps, population data, and geographic information.', 34.99, null, 20, 'BOOK-WA-003'],
];

$prodStmt = $pdo->prepare("INSERT INTO products (category_id, name, slug, description, price, sale_price, stock, sku, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?)");
foreach ($products as $p) {
    $prodStmt->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $now, $now]);
}

// Store settings
$settings = [
    'store_name' => 'OnClick2Buy',
    'store_tagline' => 'Your premier destination for quality products at unbeatable prices.',
    'store_email' => 'support@onclick2buy.com',
    'store_phone' => '+1 (555) 123-4567',
    'store_address' => '123 Commerce St, Tech City, TC 10001',
    'store_currency' => 'USD',
    'store_currency_symbol' => '$',
    'social_facebook' => '#',
    'social_twitter' => '#',
    'social_instagram' => '#',
    'social_tiktok' => '#',
    'hero_title' => 'Premium Shopping Experience',
    'hero_subtitle' => 'Discover curated collections of high-quality products with unbeatable prices and fast delivery.',
    'hero_cta_text' => 'Shop Now',
    'footer_text' => 'Made with care.',
    'announcement_text' => '',
    'announcement_enabled' => '0',
    'meta_description' => 'OnClick2Buy - Your premier destination for quality products at unbeatable prices.',
];

$settingStmt = $pdo->prepare("INSERT INTO store_settings (key, value, created_at, updated_at) VALUES (?, ?, ?, ?)");
foreach ($settings as $key => $value) {
    $settingStmt->execute([$key, $value, $now, $now]);
}

// Sample orders
$statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
$paymentMethods = ['credit_card', 'paypal', 'stripe', 'bank_transfer', 'cod'];
$paymentStatuses = ['paid', 'paid', 'paid', 'paid', 'unpaid', 'refunded'];
$addresses = [
    '123 Main St, Springfield, IL 62701',
    '456 Oak Ave, Portland, OR 97201',
    '789 Pine Rd, Austin, TX 73301',
    '321 Elm St, Denver, CO 80201',
    '654 Maple Dr, Seattle, WA 98101',
];

$prodCount = count($products);
$orderStmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, status, total, shipping_address, payment_method, payment_status, paid_at, transaction_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");

for ($i = 0; $i < 50; $i++) {
    $userId = ($i % 2) + 1;
    $status = $statuses[array_rand($statuses)];
    $payMethod = $paymentMethods[array_rand($paymentMethods)];
    $payStatus = $paymentStatuses[array_rand($paymentStatuses)];
    $daysAgo = rand(1, 90);
    $createdAt = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));
    $paidAt = $payStatus === 'paid' ? date('Y-m-d H:i:s', strtotime("-{$daysAgo} days + " . rand(1, 60) . " minutes")) : null;
    $txnId = $payStatus === 'paid' ? 'TXN-' . strtoupper(bin2hex(random_bytes(6))) : null;

    $total = 0;
    $itemCount = rand(1, 4);
    $totalItems = [];

    for ($j = 0; $j < $itemCount; $j++) {
        $prodIdx = rand(1, $prodCount);
        $prod = $products[$prodIdx - 1];
        $qty = rand(1, 3);
        $price = $prod[5] ?? $prod[4];
        $total += $price * $qty;
        $totalItems[] = [$prodIdx, $prod[1], $price, $qty];
    }

    $orderNum = 'ORD-' . strtoupper(bin2hex(random_bytes(5)));
    $orderStmt->execute([$userId, $orderNum, $status, round($total, 2), $addresses[array_rand($addresses)], $payMethod, $payStatus, $paidAt, $txnId, $createdAt, $createdAt]);
    $orderId = $pdo->lastInsertId();

    foreach ($totalItems as $item) {
        $itemStmt->execute([$orderId, $item[0], $item[1], $item[2], $item[3], $createdAt, $createdAt]);
    }
}

echo "Seeded 2 users, 5 categories, 15 products, 50 orders, and store settings!\n";
echo "Admin login: admin@admin.com / password\n";
echo "User login:  user@user.com / password\n";
