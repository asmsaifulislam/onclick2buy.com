<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOrder extends Model
{
    protected $fillable = [
        'supplier_name',
        'supplier_email',
        'supplier_phone',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
        'delivery_address',
        'required_date',
        'notes',
        'status',
        'mail_sent',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
        'mail_sent' => 'boolean',
        'required_date' => 'date',
    ];

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('supplier_name', 'like', "%{$search}%")
              ->orWhere('supplier_email', 'like', "%{$search}%")
              ->orWhere('product_name', 'like', "%{$search}%")
              ->orWhere('supplier_phone', 'like', "%{$search}%");
        });
    }
}
