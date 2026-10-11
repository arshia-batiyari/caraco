<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttractionTranslation extends Model
{
    protected $fillable = ['attraction_id', 'locale', 'title', 'slug', 'summary', 'description', 'visitor_info', 'cover_alt', 'meta_title', 'meta_description', 'canonical_url'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Attraction::class, 'attraction_id');
    }
}
