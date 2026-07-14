<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = ['name', 'rate', 'is_active'];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static function activeRate(): float
    {
        $rate = self::where('is_active', true)->orderBy('id')->value('rate');
        return $rate ? (float) $rate : 0;
    }
}
