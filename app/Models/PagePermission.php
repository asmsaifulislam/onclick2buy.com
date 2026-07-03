<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagePermission extends Model
{
    protected $fillable = ['role', 'page_key', 'can_view'];

    protected $casts = [
        'can_view' => 'boolean',
    ];

    public function scopeForRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
