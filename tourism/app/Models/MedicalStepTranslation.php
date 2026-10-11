<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalStepTranslation extends Model
{
    protected $fillable = ['medical_step_id', 'locale', 'title', 'body'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MedicalStep::class, 'medical_step_id');
    }
}
