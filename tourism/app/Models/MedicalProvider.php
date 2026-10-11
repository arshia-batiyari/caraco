<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use App\Models\Concerns\Publishes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalProvider extends Model
{
    use HasLocaleTranslation, Publishes;

    protected $fillable = ['destination_id', 'is_published', 'position', 'cover_path'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    protected function translationLabel(): string
    {
        return 'name';
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MedicalProviderTranslation::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(MedicalService::class, 'medical_provider_service');
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(MedicalDoctor::class);
    }
}
