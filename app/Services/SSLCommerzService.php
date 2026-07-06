<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class SSLCommerzService
{
    protected string $apiUrl;
    protected string $validatorUrl;
    protected string $storeId;
    protected string $storePassword;

    public function __construct()
    {
        $mode = config('sslcommerz.sandbox', true) ? 'sandbox' : 'live';
        $this->apiUrl = config("sslcommerz.urls.{$mode}.api");
        $this->validatorUrl = config("sslcommerz.urls.{$mode}.validator");
        $this->storeId = config('sslcommerz.store_id');
        $this->storePassword = config('sslcommerz.store_password');
    }

    public function initiate(Order $order, string $currency = 'BDT'): array
    {
        $user = $order->user;
        $postData = [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'total_amount' => number_format($order->total, 2, '.', ''),
            'currency' => $currency,
            'tran_id' => $order->order_number,
            'success_url' => route('sslcommerz.success', $order),
            'fail_url' => route('sslcommerz.fail', $order),
            'cancel_url' => route('sslcommerz.cancel', $order),
            'ipn_url' => route('sslcommerz.ipn', $order),
            'cus_name' => $user?->name ?? 'Guest',
            'cus_email' => $user?->email ?? 'guest@example.com',
            'cus_phone' => $user?->phone ?? '',
            'cus_add1' => $order->shipping_address,
            'cus_country' => 'Bangladesh',
            'product_name' => 'Order ' . $order->order_number,
            'product_category' => 'General',
            'product_profile' => 'general',
            'shipping_method' => 'NO',
            'num_of_item' => $order->items()->count(),
        ];

        $response = Http::asForm()->post($this->apiUrl, $postData);

        if (!$response->successful()) {
            return ['status' => 'FAILED', 'reason' => 'HTTP error: ' . $response->status()];
        }

        $result = $response->json();

        if (($result['status'] ?? '') === 'SUCCESS') {
            return [
                'status' => 'SUCCESS',
                'url' => $result['GatewayPageURL'] ?? null,
                'sessionkey' => $result['sessionkey'] ?? null,
            ];
        }

        return [
            'status' => 'FAILED',
            'reason' => $result['failedreason'] ?? 'Unknown error',
        ];
    }

    public function validate(string $valId): array
    {
        $response = Http::get($this->validatorUrl, [
            'val_id' => $valId,
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'v' => 1,
            'format' => 'json',
        ]);

        if (!$response->successful()) {
            return ['status' => 'FAILED', 'reason' => 'Validation HTTP error'];
        }

        return $response->json();
    }

    public function validateByTransactionId(string $transactionId): array
    {
        $response = Http::get($this->validatorUrl, [
            'tran_id' => $transactionId,
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'v' => 1,
            'format' => 'json',
        ]);

        if (!$response->successful()) {
            return ['status' => 'FAILED', 'reason' => 'Validation HTTP error'];
        }

        return $response->json();
    }
}
