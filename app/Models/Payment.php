<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'payment_method_id', 'method_code', 'amount',
        'transaction_id', 'sender_number', 'sender_name', 'status',
        'notes', 'admin_notes', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }

    public function scopeVerified($q)
    {
        return $q->where('status', 'verified');
    }

    public function scopeByMethod($q, $code)
    {
        return $q->where('method_code', $code);
    }
}
