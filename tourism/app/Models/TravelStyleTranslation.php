<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelStyleTranslation extends Model
{
    protected $fillable = ['locale', 'name', 'slug', 'summary'];
}
