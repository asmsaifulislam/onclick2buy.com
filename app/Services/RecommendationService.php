<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Order;
use App\Models\Review;

class RecommendationService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('recommendations.url', 'http://localhost:8081');
    }

    /**
     * Get personalized recommendations for a user
     */
    public function getRecommendations(int $userId, int $limit = 10): array
    {
        try {
            $response = Http::timeout(10)
                ->post("{$this->baseUrl}/recommendations", [
                    'user_id' => $userId,
                    'n_recommendations' => $limit,
                    'exclude_purchased' => true,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                // Enrich with product details
                $recommendations = collect($data['recommendations'] ?? []);
                $productIds = $recommendations->pluck('product_id')->toArray();

                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

                return $recommendations->map(function ($rec) use ($products) {
                    $product = $products->get($rec['product_id']);
                    return [
                        'product_id' => $rec['product_id'],
                        'product_name' => $product->name ?? $rec['product_name'] ?? 'Unknown',
                        'predicted_rating' => $rec['predicted_rating'],
                        'image' => $product->image ?? null,
                        'price' => $product->sale_price ?? $product->price ?? 0,
                        'slug' => $product->slug ?? null,
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            Log::error('Recommendation API error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get similar products
     */
    public function getSimilarProducts(int $productId, int $limit = 10): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/similar/{$productId}", [
                    'n' => $limit,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $similar = collect($data['similar_products'] ?? []);
                $productIds = $similar->pluck('product_id')->toArray();

                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

                return $similar->map(function ($item) use ($products) {
                    $product = $products->get($item['product_id']);
                    return [
                        'product_id' => $item['product_id'],
                        'product_name' => $product->name ?? $item['product_name'] ?? 'Unknown',
                        'similarity_score' => $item['similarity_score'],
                        'image' => $product->image ?? null,
                        'price' => $product->sale_price ?? $product->price ?? 0,
                        'slug' => $product->slug ?? null,
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            Log::error('Similar products API error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get popular products
     */
    public function getPopularProducts(int $limit = 10): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/popular", [
                    'n' => $limit,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $products = collect($data['products'] ?? []);
                $productIds = $products->pluck('product_id')->toArray();

                $dbProducts = Product::whereIn('id', $productIds)->get()->keyBy('id');

                return $products->map(function ($item) use ($dbProducts) {
                    $product = $dbProducts->get($item['product_id']);
                    return [
                        'product_id' => $item['product_id'],
                        'product_name' => $product->name ?? $item['product_name'] ?? 'Unknown',
                        'avg_rating' => $item['avg_rating'],
                        'rating_count' => $item['rating_count'],
                        'image' => $product->image ?? null,
                        'price' => $product->sale_price ?? $product->price ?? 0,
                        'slug' => $product->slug ?? null,
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            Log::error('Popular products API error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get trending products
     */
    public function getTrendingProducts(int $limit = 10, int $days = 30): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/trending", [
                    'n' => $limit,
                    'days' => $days,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $products = collect($data['products'] ?? []);
                $productIds = $products->pluck('product_id')->toArray();

                $dbProducts = Product::whereIn('id', $productIds)->get()->keyBy('id');

                return $products->map(function ($item) use ($dbProducts) {
                    $product = $dbProducts->get($item['product_id']);
                    return [
                        'product_id' => $item['product_id'],
                        'product_name' => $product->name ?? $item['product_name'] ?? 'Unknown',
                        'avg_rating' => $item['avg_rating'],
                        'rating_count' => $item['rating_count'],
                        'score' => $item['score'] ?? 0,
                        'image' => $product->image ?? null,
                        'price' => $product->sale_price ?? $product->price ?? 0,
                        'slug' => $product->slug ?? null,
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            Log::error('Trending products API error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Add a rating
     */
    public function addRating(int $userId, int $productId, float $rating): bool
    {
        try {
            $response = Http::timeout(10)
                ->post("{$this->baseUrl}/ratings", [
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'rating' => $rating,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Add rating API error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync ratings from Laravel to recommendation engine
     */
    public function syncRatings(): array
    {
        try {
            // Get all reviews as ratings
            $reviews = Review::all();

            $ratings = $reviews->map(function ($review) {
                return [
                    'user_id' => $review->user_id,
                    'product_id' => $review->product_id,
                    'rating' => $review->rating,
                    'timestamp' => $review->created_at->timestamp,
                ];
            })->toArray();

            if (empty($ratings)) {
                return ['success' => true, 'message' => 'No ratings to sync', 'count' => 0];
            }

            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/ratings/batch", [
                    'ratings' => $ratings,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Ratings synced successfully',
                    'count' => count($ratings),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Sync ratings error: ' . $e->getMessage());
        }

        return ['success' => false, 'message' => 'Failed to sync ratings'];
    }

    /**
     * Train the recommendation model
     */
    public function trainModel(string $algorithm = 'SVD'): array
    {
        try {
            // First sync ratings
            $this->syncRatings();

            // Update product names
            $this->syncProductNames();

            // Train model
            $response = Http::timeout(120)
                ->post("{$this->baseUrl}/train", [], [
                    'query' => ['algorithm' => $algorithm],
                ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Train model error: ' . $e->getMessage());
        }

        return ['success' => false, 'message' => 'Failed to train model'];
    }

    /**
     * Sync product names to recommendation engine
     */
    public function syncProductNames(): bool
    {
        try {
            $products = Product::all()->pluck('name', 'id')->toArray();

            $response = Http::timeout(30)
                ->post("{$this->baseUrl}/products/names", $products);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Sync product names error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get model information
     */
    public function getModelInfo(): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/model/info");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Model info error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Test connection to recommendation engine
     */
    public function testConnection(): array
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->baseUrl}/health");

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'message' => 'Connected to recommendation engine',
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Failed to connect',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get recommendations from order history (fallback)
     */
    public function getFallbackRecommendations(int $limit = 10): array
    {
        // Get popular products based on orders
        $popularProducts = Order::select('product_id', \DB::raw('COUNT(*) as order_count'))
            ->groupBy('product_id')
            ->orderByDesc('order_count')
            ->limit($limit)
            ->pluck('product_id')
            ->toArray();

        $products = Product::whereIn('id', $popularProducts)->get();

        return $products->map(function ($product) {
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'image' => $product->image,
                'price' => $product->sale_price ?? $product->price,
                'slug' => $product->slug,
            ];
        })->toArray();
    }
}
