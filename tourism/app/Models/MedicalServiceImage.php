<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalServiceImage extends Model
{
    protected $fillable = ['medical_service_id', 'path', 'position'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(MedicalService::class, 'medical_service_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(MedicalServiceImageTranslation::class);
    }

    public function altFor(string $fallback): string
    {
        $row = $this->translations->firstWhere('locale', app()->getLocale());

        return filled($row?->alt) ? (string) $row->alt : $fallback;
    }

    public function caption(): ?string
    {
        $row = $this->translations->firstWhere('locale', app()->getLocale());

        return filled($row?->caption) ? (string) $row->caption : null;
    }
}
