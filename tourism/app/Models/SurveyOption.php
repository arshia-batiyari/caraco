<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyOption extends Model
{
    use HasLocaleTranslation;

    protected $fillable = ['survey_question_id', 'position'];

    protected function translationLabel(): string
    {
        return 'label';
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SurveyOptionTranslation::class);
    }
}
