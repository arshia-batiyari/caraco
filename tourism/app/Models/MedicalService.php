<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use App\Models\Concerns\Publishes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalService extends Model
{
    use HasLocaleTranslation, Publishes;

    protected $fillable = ['medical_category_id', 'is_published', 'position', 'cover_path'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    protected function translationLabel(): string
    {
        return 'name';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicalCategory::class, 'medical_category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MedicalServiceTranslation::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(MedicalServiceImage::class)->orderBy('position');
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(MedicalProvider::class, 'medical_provider_service');
    }
}
