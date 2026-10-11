<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    protected $fillable = [
        'locale',
        'name',
        'slug',
        'summary',
        'meta_title',
        'meta_description',
        'canonical_url',
    ];
}
