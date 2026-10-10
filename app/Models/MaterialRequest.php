<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequest extends Model
{
    public const TYPES = ['cement', 'brick', 'stone', 'installations', 'rebar', 'aggregate', 'other'];

    public const FULFILLMENTS = ['purchase', 'barter', 'both'];

    protected $fillable = [
        'user_id', 'name', 'phone', 'title', 'description',
        'material_type', 'fulfillment', 'quantity_label', 'needed_on',
        'province', 'city', 'delivery_place', 'budget',
        'status', 'rejection_reason', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'needed_on' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(MaterialOffer::class);
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

    public function getLocalizedTypeAttribute(): string
    {
        return __('site.materials.types.'.$this->material_type);
    }

    public function getLocalizedFulfillmentAttribute(): string
    {
        return __('site.materials.fulfillments.'.$this->fulfillment);
    }
}
