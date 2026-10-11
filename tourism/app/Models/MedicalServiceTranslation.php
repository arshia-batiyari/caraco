<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalServiceTranslation extends Model
{
    protected $fillable = ['medical_service_id', 'locale', 'name', 'slug', 'summary', 'description', 'cover_alt', 'meta_title', 'meta_description', 'canonical_url'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MedicalService::class, 'medical_service_id');
    }
}
