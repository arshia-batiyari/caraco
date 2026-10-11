<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalCategoryTranslation extends Model
{
    protected $fillable = ['medical_category_id', 'locale', 'name', 'slug', 'summary', 'description', 'meta_title', 'meta_description', 'canonical_url'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MedicalCategory::class, 'medical_category_id');
    }
}
