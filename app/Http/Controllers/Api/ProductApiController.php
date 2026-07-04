<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductApiController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::active()->with('category')->latest()->paginate(12);
        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        if (!$product->is_active) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $product->load('category', 'reviews');
        return response()->json($product);
    }
}
