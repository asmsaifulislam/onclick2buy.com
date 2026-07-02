<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $products = Product::all();

        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentMethods = ['credit_card', 'paypal', 'stripe', 'bank_transfer', 'cod'];
        $paymentStatuses = ['paid', 'paid', 'paid', 'paid', 'unpaid', 'refunded'];

        $addresses = [
            '123 Main St, Springfield, IL 62701',
            '456 Oak Ave, Portland, OR 97201',
            '789 Pine Rd, Austin, TX 73301',
            '321 Elm St, Denver, CO 80201',
            '654 Maple Dr, Seattle, WA 98101',
            '987 Cedar Ln, Miami, FL 33101',
            '147 Birch Blvd, Boston, MA 02101',
            '258 Walnut Ct, Chicago, IL 60601',
            '369 Ash Way, New York, NY 10001',
            '741 Spruce Cir, San Francisco, CA 94101',
        ];

        // Generate orders spread across the last 3 months
        $now = now();
        $orderCount = 50;

        for ($i = 0; $i < $orderCount; $i++) {
            $userId = $userIds[array_rand($userIds)];
            $status = $statuses[array_rand($statuses)];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

            // Random date within last 90 days
            $daysAgo = rand(1, 90);
            $createdAt = $now->copy()->subDays($daysAgo)->addHours(rand(0, 23))->addMinutes(rand(0, 59));

            $paidAt = ($paymentStatus === 'paid')
                ? $createdAt->copy()->addMinutes(rand(1, 60))
                : null;

            $transactionId = ($paymentStatus === 'paid')
                ? 'TXN-' . strtoupper(Str::random(12))
                : null;

            $total = 0;
            $itemCount = rand(1, 5);
            $items = [];

            $selectedProducts = $products->random(min($itemCount, $products->count()));
            foreach ($selectedProducts as $product) {
                $qty = rand(1, 3);
                $price = $product->sale_price ?? $product->price;
                $total += $price * $qty;
                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $price,
                    'quantity' => $qty,
                ];
            }

            $order = Order::create([
                'user_id' => $userId,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'status' => $status,
                'total' => round($total, 2),
                'shipping_address' => $addresses[array_rand($addresses)],
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'paid_at' => $paidAt,
                'transaction_id' => $transactionId,
                'notes' => rand(0, 1) ? 'Leave at the door' : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);
            }
        }

        $this->command->info("Created {$orderCount} sample orders with order items.");
    }
}
