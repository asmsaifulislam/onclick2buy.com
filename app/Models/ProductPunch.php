<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPunch extends Model
{
    protected $fillable = [
        'date',
        'product_name',
        'supplier',
        'quantity',
        'unit_price',
        'total_price',
        'qc_remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('product_name', 'like', "%{$search}%")
              ->orWhere('supplier', 'like', "%{$search}%")
              ->orWhere('qc_remarks', 'like', "%{$search}%");
        });
    }
}
