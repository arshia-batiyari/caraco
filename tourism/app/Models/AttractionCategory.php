<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use App\Models\Concerns\Publishes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttractionCategory extends Model
{
    use Publishes, HasLocaleTranslation;

    protected $fillable = ['is_published', 'position'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    protected function translationLabel(): string
    {
        return 'name';
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AttractionCategoryTranslation::class);
    }
}
