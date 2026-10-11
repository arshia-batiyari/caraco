<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutePoint extends Model
{
    use HasLocaleTranslation;

    protected $fillable = [
        'tour_id',
        'itinerary_day_id',
        'position',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected function translationLabel(): string
    {
        return 'name';
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(ItineraryDay::class, 'itinerary_day_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(RoutePointTranslation::class);
    }
}
