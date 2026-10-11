<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalProviderTranslation extends Model
{
    protected $fillable = ['medical_provider_id', 'locale', 'name', 'slug', 'summary', 'description', 'cover_alt', 'meta_title', 'meta_description', 'canonical_url'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MedicalProvider::class, 'medical_provider_id');
    }
}
