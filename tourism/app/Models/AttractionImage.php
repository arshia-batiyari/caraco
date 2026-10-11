<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttractionImage extends Model
{
    protected $fillable = ['attraction_id', 'path', 'position'];

    public function attraction(): BelongsTo
    {
        return $this->belongsTo(Attraction::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AttractionImageTranslation::class);
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
