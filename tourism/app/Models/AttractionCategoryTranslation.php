<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttractionCategoryTranslation extends Model
{
    protected $fillable = ['attraction_category_id', 'locale', 'name', 'slug', 'summary'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AttractionCategory::class, 'attraction_category_id');
    }
}
