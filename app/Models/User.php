<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'is_admin', 'phone', 'role'];

    protected $hidden = ['password', 'remember_token'];

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isCustomer(): bool
    {
        return !$this->is_admin;
    }

    public function isGuest(): bool
    {
        return false;
    }

    public function canAccessPage(string $pageKey): bool
    {
        if ($this->is_admin) {
            return true;
        }
        return PagePermission::where('role', 'customer')
            ->where('page_key', $pageKey)
            ->where('can_view', true)
            ->exists();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
}
