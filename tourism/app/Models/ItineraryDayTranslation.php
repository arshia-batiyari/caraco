<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItineraryDayTranslation extends Model
{
    protected $fillable = ['locale', 'title', 'body'];
}
