<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    protected $recommendation;

    public function __construct(RecommendationService $recommendation)
    {
        $this->recommendation = $recommendation;
    }

    /**
     * Show recommendations admin page
     */
    public function index()
    {
        return view('admin.recommendations.index');
    }

    /**
     * Get personalized recommendations for current user
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $userId = Auth::id() ?? $request->input('user_id', 0);
        $limit = $request->input('limit', 10);

        if ($userId) {
            $recommendations = $this->recommendation->getRecommendations($userId, $limit);
        } else {
            $recommendations = $this->recommendation->getFallbackRecommendations($limit);
        }

        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Get recommendations for a specific user (API)
     */
    public function forUser(Request $request, int $userId): JsonResponse
    {
        $limit = $request->input('limit', 10);

        $recommendations = $this->recommendation->getRecommendations($userId, $limit);

        return response()->json([
            'success' => true,
            'user_id' => $userId,
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Get similar products
     */
    public function similar(Request $request, int $productId): JsonResponse
    {
        $limit = $request->input('limit', 10);

        $similar = $this->recommendation->getSimilarProducts($productId, $limit);

        return response()->json([
            'success' => true,
            'product_id' => $productId,
            'similar_products' => $similar,
        ]);
    }

    /**
     * Get popular products
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);

        $popular = $this->recommendation->getPopularProducts($limit);

        return response()->json([
            'success' => true,
            'products' => $popular,
        ]);
    }

    /**
     * Get trending products
     */
    public function trending(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $days = $request->input('days', 30);

        $trending = $this->recommendation->getTrendingProducts($limit, $days);

        return response()->json([
            'success' => true,
            'products' => $trending,
        ]);
    }

    /**
     * Add a rating
     */
    public function addRating(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer',
            'rating' => 'required|numeric|min:1|max:5',
        ]);

        $userId = Auth::id() ?? $request->input('user_id', 0);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'error' => 'User not authenticated',
            ], 401);
        }

        $success = $this->recommendation->addRating(
            $userId,
            $request->product_id,
            $request->rating
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Rating added' : 'Failed to add rating',
        ]);
    }

    /**
     * Test connection
     */
    public function testConnection(): JsonResponse
    {
        $result = $this->recommendation->testConnection();
        return response()->json($result);
    }

    /**
     * Get model info
     */
    public function modelInfo(): JsonResponse
    {
        $info = $this->recommendation->getModelInfo();
        return response()->json($info);
    }

    /**
     * Train model
     */
    public function trainModel(Request $request): JsonResponse
    {
        $algorithm = $request->input('algorithm', 'SVD');

        $result = $this->recommendation->trainModel($algorithm);

        return response()->json($result);
    }

    /**
     * Sync ratings
     */
    public function syncRatings(): JsonResponse
    {
        $result = $this->recommendation->syncRatings();
        return response()->json($result);
    }

    /**
     * Get recommendations for product page (widget)
     */
    public function productWidget(Request $request, int $productId): JsonResponse
    {
        $userId = Auth::id();
        $limit = $request->input('limit', 4);

        // Try personalized recommendations first
        if ($userId) {
            $recommendations = $this->recommendation->getRecommendations($userId, $limit + 1);
            // Remove the current product from recommendations
            $recommendations = array_filter($recommendations, function ($rec) use ($productId) {
                return $rec['product_id'] != $productId;
            });
            $recommendations = array_slice($recommendations, 0, $limit);
        } else {
            // Fallback to similar products
            $recommendations = $this->recommendation->getSimilarProducts($productId, $limit);
        }

        // If still no recommendations, get popular products
        if (empty($recommendations)) {
            $recommendations = $this->recommendation->getPopularProducts($limit);
        }

        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
        ]);
    }
}
