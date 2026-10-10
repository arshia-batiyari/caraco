<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faq extends Model
{
    use HasLocaleTranslation;

    protected $fillable = ['tour_id', 'is_published', 'position'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    protected function translationLabel(): string
    {
        return 'question';
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FaqTranslation::class);
    }
}
