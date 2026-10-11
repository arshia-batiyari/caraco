<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelTranslation extends Model
{
    protected $fillable = [
        'locale',
        'name',
        'slug',
        'summary',
        'description',
        'address',
        'room_details',
        'meta_title',
        'meta_description',
        'canonical_url',
    ];
}
