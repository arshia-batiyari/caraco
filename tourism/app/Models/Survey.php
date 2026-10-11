<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    use HasLocaleTranslation;

    protected $fillable = [
        'is_active',
        'is_anonymous',
        'opens_at',
        'closes_at',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_anonymous' => 'boolean',
            'opens_at' => 'date',
            'closes_at' => 'date',
        ];
    }

    protected function translationLabel(): string
    {
        return 'title';
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SurveyTranslation::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('position');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where(function (Builder $query) use ($today) {
                $query->whereNull('opens_at')->orWhereDate('opens_at', '<=', $today);
            })
            ->where(function (Builder $query) use ($today) {
                $query->whereNull('closes_at')->orWhereDate('closes_at', '>=', $today);
            });
    }
}
