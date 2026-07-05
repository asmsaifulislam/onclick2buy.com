<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ErpNextService
{
    protected $baseUrl;
    protected $apiKey;
    protected $apiSecret;
    protected $site;

    public function __construct()
    {
        $this->baseUrl = config('erpnext.base_url');
        $this->apiKey = config('erpnext.api.key');
        $this->apiSecret = config('erpnext.api.secret');
        $this->site = config('erpnext.site');
    }

    /**
     * Make API request to ERPNext
     */
    protected function request(string $method, string $endpoint, array $data = [], array $params = []): ?array
    {
        try {
            $url = "{$this->baseUrl}/api/resource/{$endpoint}";

            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }

            $response = Http::withHeaders([
                'Authorization' => 'token ' . $this->apiKey . ':' . $this->apiSecret,
                'Content-Type' => 'application/json',
            ]);

            $response = match($method) {
                'GET' => $response->get($url),
                'POST' => $response->post($url, $data),
                'PUT' => $response->put($url, $data),
                'DELETE' => $response->delete($url),
                default => null
            };

            if ($response && $response->successful()) {
                return $response->json();
            }

            Log::warning('ERPNext API response error', [
                'endpoint' => $endpoint,
                'status' => $response?->status(),
                'body' => $response?->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('ERPNext API request failed: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
            ]);
        }

        return null;
    }

    /**
     * Make RPC call to ERPNext
     */
    protected function rpc(string $method, array $args = []): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'token ' . $this->apiKey . ':' . $this->apiSecret,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/method/{$method}", $args);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('ERPNext RPC call failed: ' . $e->getMessage());
        }

        return null;
    }

    // ==================== Item/Product Operations ====================

    /**
     * Create or update item in ERPNext
     */
    public function createOrUpdateItem(array $itemData): ?array
    {
        $itemCode = $itemData['item_code'] ?? null;

        if (!$itemCode) {
            return null;
        }

        $existing = $this->getItem($itemCode);

        if ($existing) {
            return $this->updateItem($itemCode, $itemData);
        }

        return $this->createItem($itemData);
    }

    /**
     * Create new item
     */
    public function createItem(array $data): ?array
    {
        $result = $this->request('POST', 'Item', [
            'item_code' => $data['item_code'],
            'item_name' => $data['item_name'] ?? $data['item_code'],
            'item_group' => $data['item_group'] ?? config('erpnext.item_group'),
            'description' => $data['description'] ?? '',
            'stock_uom' => $data['stock_uom'] ?? 'Nos',
            'is_stock_item' => $data['is_stock_item'] ?? 1,
            'standard_rate' => $data['standard_rate'] ?? 0,
            'opening_stock' => $data['opening_stock'] ?? 0,
            'warehouse' => $data['warehouse'] ?? config('erpnext.warehouse'),
            'image' => $data['image'] ?? '',
            'custom_laravel_id' => $data['laravel_id'] ?? null,
        ]);

        return $result['data'] ?? null;
    }

    /**
     * Update existing item
     */
    public function updateItem(string $itemCode, array $data): ?array
    {
        $result = $this->request('PUT', "Item/{$itemCode}", [
            'item_name' => $data['item_name'] ?? null,
            'description' => $data['description'] ?? null,
            'standard_rate' => $data['standard_rate'] ?? null,
            'image' => $data['image'] ?? null,
        ]);

        return $result['data'] ?? null;
    }

    /**
     * Get item by code
     */
    public function getItem(string $itemCode): ?array
    {
        $result = $this->request('GET', "Item/{$itemCode}");
        return $result['data'] ?? null;
    }

    /**
     * Get all items
     */
    public function getItems(int $limit = 50, int $start = 0): ?array
    {
        return $this->request('GET', 'Item', [], [
            'limit_page_length' => $limit,
            'start' => $start,
            'fields' => json_encode(['name', 'item_code', 'item_name', 'item_group', 'standard_rate', 'stock_uom']),
        ]);
    }

    /**
     * Delete item
     */
    public function deleteItem(string $itemCode): bool
    {
        $result = $this->request('DELETE', "Item/{$itemCode}");
        return $result !== null;
    }

    // ==================== Customer Operations ====================

    /**
     * Create or update customer
     */
    public function createOrUpdateCustomer(array $customerData): ?array
    {
        $email = $customerData['email'] ?? null;

        if (!$email) {
            return null;
        }

        $customerName = $customerData['customer_name'] ?? $email;
        $existing = $this->getCustomerByName($customerName);

        if ($existing) {
            return $this->updateCustomer($customerName, $customerData);
        }

        return $this->createCustomer($customerData);
    }

    /**
     * Create new customer
     */
    public function createCustomer(array $data): ?array
    {
        $result = $this->request('POST', 'Customer', [
            'customer_name' => $data['customer_name'] ?? $data['email'],
            'customer_type' => $data['customer_type'] ?? 'Individual',
            'customer_group' => $data['customer_group'] ?? config('erpnext.customer_group'),
            'territory' => $data['territory'] ?? config('erpnext.territory'),
            'email_id' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'company_name' => $data['company'] ?? '',
            'custom_laravel_id' => $data['laravel_id'] ?? null,
        ]);

        return $result['data'] ?? null;
    }

    /**
     * Update customer
     */
    public function updateCustomer(string $customerName, array $data): ?array
    {
        $result = $this->request('PUT', "Customer/{$customerName}", [
            'email_id' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'company_name' => $data['company'] ?? null,
        ]);

        return $result['data'] ?? null;
    }

    /**
     * Get customer by name
     */
    public function getCustomerByName(string $customerName): ?array
    {
        $result = $this->request('GET', "Customer/{$customerName}");
        return $result['data'] ?? null;
    }

    /**
     * Get customer by email
     */
    public function getCustomerByEmail(string $email): ?array
    {
        $result = $this->request('GET', 'Customer', [], [
            'filters' => json_encode([['email_id', '=', $email]]),
        ]);

        $customers = $result['data'] ?? [];
        return $customers[0] ?? null;
    }

    /**
     * Get all customers
     */
    public function getCustomers(int $limit = 50, int $start = 0): ?array
    {
        return $this->request('GET', 'Customer', [], [
            'limit_page_length' => $limit,
            'start' => $start,
        ]);
    }

    // ==================== Sales Order Operations ====================

    /**
     * Create sales order
     */
    public function createSalesOrder(array $orderData): ?array
    {
        $items = [];

        foreach ($orderData['items'] as $item) {
            $items[] = [
                'item_code' => $item['item_code'],
                'qty' => $item['quantity'],
                'rate' => $item['rate'],
                'amount' => $item['amount'] ?? ($item['quantity'] * $item['rate']),
                'warehouse' => $item['warehouse'] ?? config('erpnext.warehouse'),
            ];
        }

        $result = $this->request('POST', 'Sales Order', [
            'customer' => $orderData['customer'],
            'order_date' => $orderData['order_date'] ?? date('Y-m-d'),
            'delivery_date' => $orderData['delivery_date'] ?? date('Y-m-d', strtotime('+7 days')),
            'company' => config('erpnext.company'),
            'currency' => $orderData['currency'] ?? 'USD',
            'selling_price_list' => $orderData['price_list'] ?? 'Standard Selling',
            'items' => $items,
            'custom_laravel_order_id' => $orderData['laravel_order_id'] ?? null,
            'custom_laravel_order_number' => $orderData['order_number'] ?? null,
        ]);

        return $result['data'] ?? null;
    }

    /**
     * Get sales order
     */
    public function getSalesOrder(string $orderName): ?array
    {
        $result = $this->request('GET', "Sales Order/{$orderName}");
        return $result['data'] ?? null;
    }

    /**
     * Get all sales orders
     */
    public function getSalesOrders(int $limit = 50, int $start = 0): ?array
    {
        return $this->request('GET', 'Sales Order', [], [
            'limit_page_length' => $limit,
            'start' => $start,
        ]);
    }

    /**
     * Update sales order status
     */
    public function updateSalesOrderStatus(string $orderName, string $status): ?array
    {
        $result = $this->request('PUT', "Sales Order/{$orderName}", [
            'status' => $status,
        ]);

        return $result['data'] ?? null;
    }

    // ==================== Stock/Inventory Operations ====================

    /**
     * Get stock balance
     */
    public function getStockBalance(string $itemCode, string $warehouse = ''): ?array
    {
        $warehouse = $warehouse ?: config('erpnext.warehouse');

        $result = $this->rpc('erpnext.stock.utils.get_stock_balance', [
            'item_code' => $itemCode,
            'warehouse' => $warehouse,
        ]);

        return $result;
    }

    /**
     * Get all stock balances
     */
    public function getAllStockBalances(string $warehouse = ''): ?array
    {
        $warehouse = $warehouse ?: config('erpnext.warehouse');

        $result = $this->rpc('erpnext.stock.utils.get_stock_balance_for_item', [
            'warehouse' => $warehouse,
        ]);

        return $result;
    }

    /**
     * Create stock entry (for inventory adjustments)
     */
    public function createStockEntry(array $data): ?array
    {
        $items = [];

        foreach ($data['items'] as $item) {
            $items[] = [
                'item_code' => $item['item_code'],
                'qty' => $item['quantity'],
                's_warehouse' => $item['source_warehouse'] ?? config('erpnext.warehouse'),
                't_warehouse' => $item['target_warehouse'] ?? config('erpnext.warehouse'),
            ];
        }

        $result = $this->request('POST', 'Stock Entry', [
            'stock_entry_type' => $data['type'] ?? 'Material Transfer',
            'company' => config('erpnext.company'),
            'items' => $items,
        ]);

        return $result['data'] ?? null;
    }

    // ==================== Warehouse Operations ====================

    /**
     * Get all warehouses
     */
    public function getWarehouses(): ?array
    {
        return $this->request('GET', 'Warehouse', [], [
            'filters' => json_encode([['company', '=', config('erpnext.company')]]),
        ]);
    }

    // ==================== Company Operations ====================

    /**
     * Get company details
     */
    public function getCompany(): ?array
    {
        $result = $this->request('GET', 'Company/' . config('erpnext.company'));
        return $result['data'] ?? null;
    }

    // ==================== Connection Test ====================

    /**
     * Test ERPNext connection
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'token ' . $this->apiKey . ':' . $this->apiSecret,
            ])->get("{$this->baseUrl}/api/method/frappe.client.get_count", [
                'doctype' => 'User',
            ]);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'message' => 'Connected to ERPNext successfully',
                    'version' => $this->getVersion(),
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Failed to connect to ERPNext',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get ERPNext version
     */
    public function getVersion(): ?string
    {
        try {
            $response = Http::get("{$this->baseUrl}/api/method/frappe.utils.change_log.get_versions");

            if ($response->successful()) {
                $data = $response->json('message');
                return $data['erpnext']['branch'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get ERPNext version: ' . $e->getMessage());
        }

        return null;
    }

    // ==================== Sync Operations ====================

    /**
     * Sync Laravel product to ERPNext
     */
    public function syncProduct($product): ?array
    {
        $data = [
            'item_code' => 'PRODUCT-' . $product->id,
            'item_name' => $product->name,
            'item_group' => $product->category->name ?? config('erpnext.item_group'),
            'description' => $product->description ?? '',
            'stock_uom' => 'Nos',
            'is_stock_item' => 1,
            'standard_rate' => $product->sale_price ?: $product->price,
            'opening_stock' => $product->stock,
            'warehouse' => config('erpnext.warehouse'),
            'image' => $product->image ? asset('storage/' . $product->image) : '',
            'laravel_id' => $product->id,
        ];

        return $this->createOrUpdateItem($data);
    }

    /**
     * Sync Laravel user to ERPNext customer
     */
    public function syncCustomer($user): ?array
    {
        $data = [
            'customer_name' => $user->name,
            'customer_type' => 'Individual',
            'customer_group' => config('erpnext.customer_group'),
            'territory' => config('erpnext.territory'),
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'laravel_id' => $user->id,
        ];

        return $this->createOrUpdateCustomer($data);
    }

    /**
     * Sync Laravel order to ERPNext sales order
     */
    public function syncOrder($order): ?array
    {
        $customer = $this->syncCustomer($order->user);

        if (!$customer) {
            return null;
        }

        $items = [];

        foreach ($order->items as $item) {
            $items[] = [
                'item_code' => 'PRODUCT-' . $item->product_id,
                'quantity' => $item->quantity,
                'rate' => $item->price,
                'amount' => $item->quantity * $item->price,
            ];
        }

        return $this->createSalesOrder([
            'customer' => $customer['name'],
            'order_date' => $order->created_at->format('Y-m-d'),
            'items' => $items,
            'currency' => 'USD',
            'laravel_order_id' => $order->id,
            'order_number' => $order->order_number,
        ]);
    }
}
