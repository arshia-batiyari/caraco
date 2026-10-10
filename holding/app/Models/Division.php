<?php

namespace App\Models;

use App\Models\Concerns\HasLocaleTranslation;
use App\Support\HttpUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasLocaleTranslation;

    protected $fillable = [
        'key',
        'position',
        'is_published',
        'destination_url',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DivisionTranslation::class);
    }

    public function externalUrl(): ?string
    {
        if (HttpUrl::valid($this->destination_url)) {
            return $this->destination_url;
        }

        if ($this->key === 'tourism' && HttpUrl::valid(config('karako.related_url'))) {
            return config('karako.related_url');
        }

        return null;
    }
}
