<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'description', 'price', 'sale_price', 'stock', 'sku', 'images', 'is_active'];
    protected $casts = ['images' => 'array', 'price' => 'decimal:2', 'sale_price' => 'decimal:2'];
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
