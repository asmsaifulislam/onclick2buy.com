<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'user@user.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        $categories = [
            ['name' => 'Electronics', 'description' => 'Gadgets, devices, and tech accessories'],
            ['name' => 'Clothing', 'description' => 'Fashion apparel for men and women'],
            ['name' => 'Home & Garden', 'description' => 'Furniture, decor, and gardening tools'],
            ['name' => 'Sports & Outdoors', 'description' => 'Sports equipment and outdoor gear'],
            ['name' => 'Books', 'description' => 'Fiction, non-fiction, and educational books'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'name' => $cat['name'],
                'slug' => Str::slug($cat['name']),
                'description' => $cat['description'],
                'is_active' => true,
            ]);
        }

        $products = [
            ['category' => 'Electronics', 'name' => 'Wireless Bluetooth Headphones', 'description' => 'Premium noise-cancelling wireless headphones with 30-hour battery life and crystal-clear sound quality.', 'price' => 89.99, 'sale_price' => 69.99, 'stock' => 50, 'sku' => 'ELEC-BT-001'],
            ['category' => 'Electronics', 'name' => 'Smart Watch Pro', 'description' => 'Advanced smartwatch with heart rate monitor, GPS tracking, and 7-day battery life.', 'price' => 199.99, 'sale_price' => null, 'stock' => 30, 'sku' => 'ELEC-SW-002'],
            ['category' => 'Electronics', 'name' => 'USB-C Hub 7-in-1', 'description' => 'Multi-port USB-C hub with HDMI 4K, USB 3.0, SD card reader, and PD charging.', 'price' => 34.99, 'sale_price' => 27.99, 'stock' => 100, 'sku' => 'ELEC-USB-003'],
            ['category' => 'Clothing', 'name' => 'Classic Denim Jacket', 'description' => 'Timeless denim jacket made from premium cotton. Perfect for any casual occasion.', 'price' => 59.99, 'sale_price' => 44.99, 'stock' => 40, 'sku' => 'CLTH-DJ-001'],
            ['category' => 'Clothing', 'name' => 'Cotton T-Shirt Pack (3-Pack)', 'description' => 'Soft, breathable 100% organic cotton t-shirts. Available in assorted colors.', 'price' => 29.99, 'sale_price' => null, 'stock' => 80, 'sku' => 'CLTH-TS-002'],
            ['category' => 'Clothing', 'name' => 'Running Shoes Ultra', 'description' => 'Lightweight running shoes with responsive cushioning and breathable mesh upper.', 'price' => 119.99, 'sale_price' => 89.99, 'stock' => 25, 'sku' => 'CLTH-RS-003'],
            ['category' => 'Home & Garden', 'name' => 'Indoor Plant Pot Set', 'description' => 'Set of 3 minimalist ceramic plant pots with drainage holes. Perfect for succulents.', 'price' => 24.99, 'sale_price' => null, 'stock' => 60, 'sku' => 'HOME-PT-001'],
            ['category' => 'Home & Garden', 'name' => 'Stainless Steel Water Bottle', 'description' => 'Double-wall insulated water bottle. Keeps drinks cold 24h or hot 12h. 750ml.', 'price' => 22.99, 'sale_price' => 18.99, 'stock' => 90, 'sku' => 'HOME-WB-002'],
            ['category' => 'Home & Garden', 'name' => 'LED Desk Lamp', 'description' => 'Adjustable LED desk lamp with 5 brightness levels, USB charging port, and clamp base.', 'price' => 39.99, 'sale_price' => null, 'stock' => 45, 'sku' => 'HOME-LM-003'],
            ['category' => 'Sports & Outdoors', 'name' => 'Yoga Mat Premium', 'description' => 'Extra-thick non-slip yoga mat with carrying strap. 6mm thickness for maximum comfort.', 'price' => 32.99, 'sale_price' => null, 'stock' => 35, 'sku' => 'SPRT-YM-001'],
            ['category' => 'Sports & Outdoors', 'name' => 'Resistance Bands Set', 'description' => 'Set of 5 resistance bands with different tension levels. Includes door anchor and carry bag.', 'price' => 19.99, 'sale_price' => 14.99, 'stock' => 70, 'sku' => 'SPRT-RB-002'],
            ['category' => 'Sports & Outdoors', 'name' => 'Camping Tent 4-Person', 'description' => 'Waterproof 4-person camping tent with easy setup. Includes rainfly and carry bag.', 'price' => 149.99, 'sale_price' => 119.99, 'stock' => 15, 'sku' => 'SPRT-CT-003'],
            ['category' => 'Books', 'name' => 'The Art of Programming', 'description' => 'A comprehensive guide to software design patterns and clean code principles.', 'price' => 42.99, 'sale_price' => null, 'stock' => 55, 'sku' => 'BOOK-PR-001'],
            ['category' => 'Books', 'name' => 'Mindful Living', 'description' => 'Practical guide to mindfulness and meditation for a balanced modern life.', 'price' => 15.99, 'sale_price' => 12.99, 'stock' => 40, 'sku' => 'BOOK-ML-002'],
            ['category' => 'Books', 'name' => 'World Atlas 2025', 'description' => 'Fully updated world atlas with detailed maps, population data, and geographic information.', 'price' => 34.99, 'sale_price' => null, 'stock' => 20, 'sku' => 'BOOK-WA-003'],
        ];

        foreach ($products as $product) {
            $category = Category::where('name', $product['category'])->first();
            Product::create([
                'category_id' => $category->id,
                'name' => $product['name'],
                'slug' => Str::slug($product['name']),
                'description' => $product['description'],
                'price' => $product['price'],
                'sale_price' => $product['sale_price'],
                'stock' => $product['stock'],
                'sku' => $product['sku'],
                'is_active' => true,
            ]);
        }

        $this->call(OrderSeeder::class);
    }
}
