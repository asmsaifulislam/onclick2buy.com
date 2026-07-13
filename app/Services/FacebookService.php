<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FacebookService
{
    protected string $pageAccessToken;
    protected string $pageId;
    protected string $graphVersion = 'v19.0';

    public function __construct()
    {
        $this->pageAccessToken = StoreSetting::get('fb_page_access_token', '');
        $this->pageId = StoreSetting::get('fb_page_id', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->pageAccessToken) && !empty($this->pageId);
    }

    public function publishProduct(Product $product): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Facebook is not configured. Please add your Page Access Token and Page ID in Settings.'];
        }

        $message = $this->buildMessage($product);
        $imageUrl = $product->images[0] ?? null;
        $productUrl = route('products.show', $product);

        try {
            if ($imageUrl) {
                return $this->postWithImage($imageUrl, $message, $productUrl);
            }

            return $this->postTextOnly($message, $productUrl);
        } catch (\Exception $e) {
            Log::error('Facebook publish failed: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to publish: ' . $e->getMessage()];
        }
    }

    protected function postWithImage(string $imageUrl, string $message, string $link): array
    {
        $response = Http::attach(
            'source',
            file_get_contents($imageUrl),
            basename(parse_url($imageUrl, PHP_URL_PATH))
        )->post("https://graph.facebook.com/{$this->graphVersion}/{$this->pageId}/photos", [
            'message' => $message . "\n\n🔗 " . $link,
            'access_token' => $this->pageAccessToken,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'post_id' => $data['id'] ?? null,
                'message' => 'Product published to Facebook successfully!',
            ];
        }

        $error = $response->json('error.message', 'Unknown error');
        Log::error('Facebook API error: ' . $error);
        return ['success' => false, 'error' => $error];
    }

    protected function postTextOnly(string $message, string $link): array
    {
        $response = Http::post("https://graph.facebook.com/{$this->graphVersion}/{$this->pageId}/feed", [
            'message' => $message . "\n\n🔗 " . $link,
            'access_token' => $this->pageAccessToken,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'post_id' => $data['id'] ?? null,
                'message' => 'Product published to Facebook successfully!',
            ];
        }

        $error = $response->json('error.message', 'Unknown error');
        Log::error('Facebook API error: ' . $error);
        return ['success' => false, 'error' => $error];
    }

    protected function buildMessage(Product $product): string
    {
        $currency = StoreSetting::get('store_currency_symbol', '$');
        $storeName = StoreSetting::get('store_name', config('app.name'));
        $price = $product->sale_price ?: $product->price;

        $message = "🛍️ {$product->name}\n\n";

        if ($product->description) {
            $description = Str::limit(strip_tags($product->description), 200);
            $message .= $description . "\n\n";
        }

        $message .= "💰 Price: {$currency}" . number_format($price, 2);
        if ($product->sale_price) {
            $original = StoreSetting::get('store_currency_symbol', '$') . number_format($product->price, 2);
            $message .= " (was {$original})";
        }
        $message .= "\n";

        if ($product->sku) {
            $message .= "📦 SKU: {$product->sku}\n";
        }

        if ($product->stock > 0) {
            $message .= "✅ In Stock ({$product->stock} available)\n";
        } else {
            $message .= "❌ Out of Stock\n";
        }

        $message .= "\n🛒 Shop now at {$storeName}";

        return $message;
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Facebook is not configured.'];
        }

        try {
            $response = Http::get("https://graph.facebook.com/{$this->graphVersion}/{$this->pageId}", [
                'fields' => 'name,id',
                'access_token' => $this->pageAccessToken,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'page_name' => $data['name'] ?? 'Unknown',
                    'page_id' => $data['id'] ?? $this->pageId,
                    'message' => 'Connected to Facebook Page: ' . ($data['name'] ?? 'Unknown'),
                ];
            }

            $error = $response->json('error.message', 'Unknown error');
            return ['success' => false, 'error' => $error];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
