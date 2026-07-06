<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class BangladeshPaymentMethodsSeeder extends Seeder
{
    public function run()
    {
        $methods = [
            [
                'code' => 'bkash',
                'name' => 'bKash',
                'description' => 'Pay with bKash mobile banking',
                'account_number' => '01XXXXXXXXX',
                'account_name' => 'OnClick2Buy',
                'icon' => 'bkash',
                'instructions' => [
                    'Open your bKash app or dial *247#',
                    'Select "Send Money"',
                    'Enter the merchant number above',
                    'Enter the exact amount shown',
                    'Enter your reference (Order number)',
                    'Submit and share the Transaction ID below',
                ],
                'sort_order' => 1,
            ],
            [
                'code' => 'nagad',
                'name' => 'Nagad',
                'description' => 'Pay with Nagad mobile banking',
                'account_number' => '01XXXXXXXXX',
                'account_name' => 'OnClick2Buy',
                'icon' => 'nagad',
                'instructions' => [
                    'Open your Nagad app or dial *167#',
                    'Select "Send Money"',
                    'Enter the merchant number above',
                    'Enter the exact amount shown',
                    'Enter your reference (Order number)',
                    'Submit and share the Transaction ID below',
                ],
                'sort_order' => 2,
            ],
            [
                'code' => 'rocket',
                'name' => 'Rocket',
                'description' => 'Pay with Rocket (Dutch-Bangla Bank)',
                'account_number' => '01XXXXXXXXX',
                'account_name' => 'OnClick2Buy',
                'icon' => 'rocket',
                'instructions' => [
                    'Open your Rocket app or dial *322#',
                    'Select "Send Money"',
                    'Enter the merchant number above',
                    'Enter the exact amount shown',
                    'Enter your reference (Order number)',
                    'Submit and share the Transaction ID below',
                ],
                'sort_order' => 3,
            ],
            [
                'code' => 'card',
                'name' => 'Debit / Credit Card',
                'description' => 'Visa, Mastercard, Debit Cards accepted',
                'account_number' => null,
                'account_name' => null,
                'icon' => 'card',
                'instructions' => [
                    'Enter your card details below',
                    'We accept Visa, Mastercard, and all Debit cards',
                    'Your payment is processed securely',
                ],
                'sort_order' => 4,
            ],
            [
                'code' => 'cod',
                'name' => 'Cash on Delivery',
                'description' => 'Pay when you receive your order',
                'account_number' => null,
                'account_name' => null,
                'icon' => 'cod',
                'instructions' => [
                    'No advance payment needed',
                    'Pay in cash when your order arrives',
                    'Our delivery agent will collect the payment',
                ],
                'sort_order' => 5,
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
