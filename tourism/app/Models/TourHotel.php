<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourHotel extends Model
{
    use HasLocaleTranslation;

    protected $fillable = ['tour_id', 'hotel_id', 'position'];

    protected function translationLabel(): string
    {
        return 'arrangement';
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TourHotelTranslation::class);
    }
}
