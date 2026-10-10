<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'locale', 'phone_verified_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function booth(): HasOne
    {
        return $this->hasOne(Booth::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isLandowner(): bool
    {
        return $this->role === 'landowner';
    }
}
