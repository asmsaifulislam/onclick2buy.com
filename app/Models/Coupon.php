<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_subtotal', 'max_discount',
        'starts_at', 'expires_at', 'usage_limit', 'used_count', 'is_active',
    ];

    protected $casts = [
        'min_subtotal' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'value' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isValid(float $subtotal): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        if ($this->min_subtotal > 0 && $subtotal < $this->min_subtotal) {
            return false;
        }
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }
        return true;
    }

    public function discountAmount(float $subtotal): float
    {
        if (!$this->isValid($subtotal)) {
            return 0;
        }
        if ($this->type === 'percent') {
            $discount = $subtotal * ($this->value / 100);
        } else {
            $discount = $this->value;
        }
        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }
        return min($discount, $subtotal);
    }
}
