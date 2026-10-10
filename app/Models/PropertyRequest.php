<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyRequest extends Model
{
    protected $fillable = [
        'user_id', 'name', 'phone', 'title', 'description',
        'property_type', 'deal_type', 'min_area_sqm', 'max_area_sqm',
        'province', 'city', 'budget', 'status', 'rejection_reason', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** فقط برای ادمین — هیچ‌جای دیگه‌ای expose نمی‌شه */
    public function offers(): HasMany
    {
        return $this->hasMany(PropertyRequestOffer::class);
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

    public function getLocalizedPropertyTypeAttribute(): string
    {
        return __('site.properties.types.'.$this->property_type);
    }

    public function getLocalizedDealTypeAttribute(): string
    {
        return __('site.properties.deal_types.'.$this->deal_type);
    }

    public function getAreaRangeLabelAttribute(): ?string
    {
        if ($this->min_area_sqm && $this->max_area_sqm) {
            return number_format($this->min_area_sqm).' - '.number_format($this->max_area_sqm);
        }

        return $this->min_area_sqm ? number_format($this->min_area_sqm).'+' : ($this->max_area_sqm ? 'تا '.number_format($this->max_area_sqm) : null);
    }
}
