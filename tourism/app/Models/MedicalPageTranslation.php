<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalPageTranslation extends Model
{
    protected $fillable = [
        'medical_page_id', 'locale', 'title', 'summary', 'body', 'travel_assistance',
        'stay_assistance', 'contact_note', 'meta_title', 'meta_description', 'canonical_url',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(MedicalPage::class, 'medical_page_id');
    }
}
