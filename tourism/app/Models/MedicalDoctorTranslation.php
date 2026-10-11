<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalDoctorTranslation extends Model
{
    protected $fillable = ['medical_doctor_id', 'locale', 'name', 'slug', 'specialty', 'biography', 'image_alt', 'meta_title', 'meta_description', 'canonical_url'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MedicalDoctor::class, 'medical_doctor_id');
    }
}
