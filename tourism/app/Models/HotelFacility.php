<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotelFacility extends Model
{
    use HasLocaleTranslation;

    protected $fillable = ['hotel_id', 'position'];

    protected function translationLabel(): string
    {
        return 'name';
    }

    public function translations(): HasMany
    {
        return $this->hasMany(HotelFacilityTranslation::class);
    }
}
