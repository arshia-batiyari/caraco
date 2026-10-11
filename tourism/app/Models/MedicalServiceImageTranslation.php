<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalServiceImageTranslation extends Model
{
    protected $fillable = ['medical_service_image_id', 'locale', 'alt', 'caption'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MedicalServiceImage::class, 'medical_service_image_id');
    }
}
