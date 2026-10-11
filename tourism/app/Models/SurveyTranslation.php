<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyTranslation extends Model
{
    protected $fillable = ['survey_id', 'locale', 'title', 'description'];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }
}
