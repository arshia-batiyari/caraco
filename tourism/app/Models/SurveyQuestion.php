<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyQuestion extends Model
{
    use HasLocaleTranslation;

    protected $fillable = ['survey_id', 'type', 'is_required', 'position'];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    protected function translationLabel(): string
    {
        return 'prompt';
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SurveyQuestionTranslation::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(SurveyOption::class)->orderBy('position');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }
}
