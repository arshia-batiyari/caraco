<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourTranslation extends Model
{
    protected $fillable = [
        'locale',
        'title',
        'slug',
        'summary',
        'description',
        'departure_location',
        'meeting_instructions',
        'transport_details',
        'meals',
        'notes',
        'requirements',
        'restrictions',
        'terms',
        'cancellation',
        'meta_title',
        'meta_description',
        'canonical_url',
    ];
}
