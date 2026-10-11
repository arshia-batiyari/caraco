<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DestinationTranslation extends Model
{
    protected $fillable = [
        'locale',
        'name',
        'slug',
        'summary',
        'description',
        'meta_title',
        'meta_description',
        'canonical_url',
    ];
}
