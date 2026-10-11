<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalFaqTranslation extends Model
{
    protected $fillable = ['medical_faq_id', 'locale', 'question', 'answer'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MedicalFaq::class, 'medical_faq_id');
    }
}
