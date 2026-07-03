<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatSession extends Model
{
    protected $fillable = ['user_id', 'visitor_name', 'visitor_email', 'visitor_id', 'status', 'last_message_at'];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class)->latest();
    }

    public function unreadCount(): int
    {
        return $this->messages()->where('is_admin', true)->where('is_read', false)->count();
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $this->visitor_name ?: 'Guest #' . $this->id;
    }
}
