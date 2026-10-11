<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalPage extends Model
{
    use HasLocaleTranslation;

    protected function translationLabel(): string
    {
        return 'title';
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MedicalPageTranslation::class);
    }

    public function localeCopy(): ?MedicalPageTranslation
    {
        return $this->translations->firstWhere('locale', app()->getLocale());
    }
}
