<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use App\Models\Concerns\Publishes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalDoctor extends Model
{
    use HasLocaleTranslation, Publishes;

    protected $fillable = ['medical_provider_id', 'is_published', 'position', 'image_path'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    protected function translationLabel(): string
    {
        return 'name';
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(MedicalProvider::class, 'medical_provider_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MedicalDoctorTranslation::class);
    }
}
