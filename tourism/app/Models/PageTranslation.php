<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model
{
    protected $fillable = [
        'locale',
        'title',
        'slug',
        'body',
        'meta_title',
        'meta_description',
        'canonical_url',
    ];
}
