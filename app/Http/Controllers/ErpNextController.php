<?php

namespace App\Http\Controllers;

use App\Services\ErpNextService;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ErpNextController extends Controller
{
    protected $erpnext;

    public function __construct(ErpNextService $erpnext)
    {
        $this->erpnext = $erpnext;
    }

    /**
     * Show ERPNext admin page
     */
    public function index()
    {
        return view('admin.erpnext.index');
    }

    /**
     * Test ERPNext connection
     */
    public function testConnection(): JsonResponse
    {
        $result = $this->erpnext->testConnection();
        return response()->json($result);
    }

    /**
     * Sync products to ERPNext
     */
    public function syncProducts(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $products = Product::take($limit)->get();

        $synced = 0;
        $failed = 0;

        foreach ($products as $product) {
            try {
                $result = $this->erpnext->syncProduct($product);
                if ($result) {
                    $synced++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'synced' => $synced,
            'failed' => $failed,
            'total' => $products->count(),
        ]);
    }

    /**
     * Sync single product
     */
    public function syncProduct(Product $product): JsonResponse
    {
        $result = $this->erpnext->syncProduct($product);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Product synced successfully',
                'item_code' => $result['name'] ?? null,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to sync product',
        ], 500);
    }

    /**
     * Sync customers to ERPNext
     */
    public function syncCustomers(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $users = User::whereNotNull('email')->take($limit)->get();

        $synced = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                $result = $this->erpnext->syncCustomer($user);
                if ($result) {
                    $synced++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'synced' => $synced,
            'failed' => $failed,
            'total' => $users->count(),
        ]);
    }

    /**
     * Sync orders to ERPNext
     */
    public function syncOrders(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $orders = Order::with(['user', 'items.product'])->take($limit)->get();

        $synced = 0;
        $failed = 0;

        foreach ($orders as $order) {
            try {
                $result = $this->erpnext->syncOrder($order);
                if ($result) {
                    $synced++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'synced' => $synced,
            'failed' => $failed,
            'total' => $orders->count(),
        ]);
    }

    /**
     * Sync inventory from ERPNext
     */
    public function syncInventory(): JsonResponse
    {
        $stockData = $this->erpnext->getAllStockBalances();

        if (!$stockData) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch inventory from ERPNext',
            ], 500);
        }

        $synced = 0;

        foreach ($stockData['message'] ?? [] as $item) {
            $itemCode = $item['item_code'] ?? null;
            $qty = $item['qty'] ?? 0;

            if ($itemCode && str_starts_with($itemCode, 'PRODUCT-')) {
                $laravelId = (int) str_replace('PRODUCT-', '', $itemCode);
                $product = Product::find($laravelId);

                if ($product) {
                    $product->update(['stock' => (int) $qty]);
                    $synced++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'synced' => $synced,
        ]);
    }

    /**
     * Get items from ERPNext
     */
    public function getItems(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 50);
        $start = $request->input('start', 0);

        $items = $this->erpnext->getItems($limit, $start);

        return response()->json($items);
    }

    /**
     * Get customers from ERPNext
     */
    public function getCustomers(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 50);
        $start = $request->input('start', 0);

        $customers = $this->erpnext->getCustomers($limit, $start);

        return response()->json($customers);
    }

    /**
     * Get sales orders from ERPNext
     */
    public function getSalesOrders(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 50);
        $start = $request->input('start', 0);

        $orders = $this->erpnext->getSalesOrders($limit, $start);

        return response()->json($orders);
    }

    /**
     * Get warehouses from ERPNext
     */
    public function getWarehouses(): JsonResponse
    {
        $warehouses = $this->erpnext->getWarehouses();

        return response()->json($warehouses);
    }

    /**
     * Get stock balance
     */
    public function getStockBalance(Request $request): JsonResponse
    {
        $request->validate([
            'item_code' => 'required|string',
        ]);

        $result = $this->erpnext->getStockBalance($request->item_code);

        return response()->json($result);
    }
}
