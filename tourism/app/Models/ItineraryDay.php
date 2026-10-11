<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItineraryDay extends Model
{
    use HasLocaleTranslation;

    protected $fillable = ['tour_id', 'day_number'];

    public function translations(): HasMany
    {
        return $this->hasMany(ItineraryDayTranslation::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(RoutePoint::class)->orderBy('position');
    }
}
