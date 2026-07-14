<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'description', 'price', 'sale_price', 'stock', 'sku', 'images', 'is_active', 'meta_title', 'meta_description', 'meta_keywords', 'variants'];
    protected $casts = ['images' => 'array', 'variants' => 'array', 'price' => 'decimal:2', 'sale_price' => 'decimal:2'];
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }
    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function matchVariant(array $attrs)
    {
        if (empty($attrs)) {
            return null;
        }
        foreach ($this->productVariants as $variant) {
            $va = $variant->attributes ?? [];
            $match = true;
            foreach ($attrs as $k => $v) {
                if (!isset($va[$k]) || (string) $va[$k] !== (string) $v) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                return $variant;
            }
        }
        return null;
    }
    public function priceForVariant(?ProductVariant $variant): float
    {
        if ($variant && $variant->price_override) {
            return (float) $variant->price_override;
        }
        return (float) ($this->sale_price ?: $this->price);
    }
    public function isWishlisted(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        return $this->wishlists()->where('user_id', auth()->id())->exists();
    }
    public function avgRating(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function getImagesAttribute($value)
    {
        $images = json_decode($value, true) ?? [];
        return array_map(fn($img) => asset('storage/' . $img), $images);
    }
}
