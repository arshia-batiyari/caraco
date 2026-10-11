<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    use HasLocaleTranslation;

    protected $fillable = [
        'destination_id',
        'accommodation_category',
        'is_published',
        'is_featured',
        'position',
        'latitude',
        'longitude',
        'website',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected function translationLabel(): string
    {
        return 'name';
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(HotelTranslation::class);
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(HotelFacility::class)->orderBy('position');
    }

    public function images(): HasMany
    {
        return $this->hasMany(HotelImage::class)->orderBy('position');
    }
}
