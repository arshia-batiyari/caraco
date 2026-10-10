<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Property extends Model
{
    protected $fillable = [
        'user_id', 'title', 'description', 'property_type', 'deal_type',
        'area_sqm', 'province', 'city', 'address', 'document_type',
        'price', 'images', 'status', 'rejection_reason', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'images' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * عمداً از سمت Property به inquiries رابطه‌ای برای پنل زمین‌دار
     * expose نمی‌شود — این متد فقط برای استفاده در کنترلرهای ادمین است.
     */
    public function inquiries(): HasMany
    {
        return $this->hasMany(PropertyInquiry::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getLocalizedTitleAttribute(): string
    {
        return $this->title[app()->getLocale()] ?? $this->title['fa'] ?? '';
    }

    public function getLocalizedDescriptionAttribute(): string
    {
        return $this->description[app()->getLocale()] ?? $this->description['fa'] ?? '';
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->map(fn ($path) => Storage::disk('public')->url($path))
            ->all();
    }

    public function getFirstImageUrlAttribute(): string
    {
        $urls = $this->image_urls;

        if (! empty($urls)) {
            return $urls[0];
        }

        $slug = match ($this->property_type) {
            'land' => 'real-estate-land',
            'villa' => 'real-estate-villa',
            'apartment' => 'real-estate-apartment',
            default => 'real-estate',
        };

        return asset('images/placeholders/banners/'.(
            file_exists(public_path('images/placeholders/banners/'.$slug.'.webp')) ? $slug : 'default'
        ).'.webp');
    }

    public function getLocalizedPropertyTypeAttribute(): string
    {
        return __('site.properties.types.'.$this->property_type);
    }

    public function getLocalizedDealTypeAttribute(): string
    {
        return __('site.properties.deal_types.'.$this->deal_type);
    }
}