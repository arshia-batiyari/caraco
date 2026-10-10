<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = ['phone', 'code', 'attempts', 'expires_at', 'used_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public static function generateFor(string $phone): self
    {
        // کدهای قبلیِ استفاده‌نشده برای این شماره رو باطل می‌کنیم
        static::where('phone', $phone)->whereNull('used_at')->delete();

        return static::create([
            'phone' => $phone,
            'code' => (string) random_int(10000, 99999), // کد ۵ رقمی
            'expires_at' => now()->addMinutes(2),
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return is_null($this->used_at) && ! $this->isExpired() && $this->attempts < 5;
    }
}
