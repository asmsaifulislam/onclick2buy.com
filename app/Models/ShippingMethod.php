<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    protected $fillable = ['name', 'cost', 'free_over', 'is_active', 'sort_order'];

    protected $casts = [
        'cost' => 'decimal:2',
        'free_over' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    public function costFor(float $subtotal): float
    {
        if ($this->free_over && $subtotal >= $this->free_over) {
            return 0;
        }
        return (float) $this->cost;
    }
}
