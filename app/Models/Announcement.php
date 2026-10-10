<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title', 'message', 'type', 'link_url', 'link_text',
        'priority', 'is_active', 'starts_at', 'ends_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'message' => 'array',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * اگهی‌هایی که الان واقعاً باید نمایش داده بشن: فعال باشن و اگه
     * بازه‌ی زمانی (starts_at/ends_at) دارن، همین لحظه توی اون بازه باشیم.
     */
    public function scopeVisible($query)
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    /** اگهی‌ای که الان باید بالای سایت نشون داده بشه — بر اساس اولویت، بعد جدیدترین */
    public static function current(): ?self
    {
        return static::visible()->orderByDesc('priority')->latest()->first();
    }

    public function getLocalizedTitleAttribute(): string
    {
        return $this->title[app()->getLocale()] ?? $this->title['fa'] ?? '';
    }

    public function getLocalizedMessageAttribute(): ?string
    {
        if (! $this->message) {
            return null;
        }

        return $this->message[app()->getLocale()] ?? $this->message['fa'] ?? null;
    }

    public function getIsCurrentlyVisibleAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }
}