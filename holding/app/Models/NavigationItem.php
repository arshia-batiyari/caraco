<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationItem extends Model
{
    protected $fillable = [
        'location',
        'kind',
        'route_name',
        'page_id',
        'url',
        'position',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(NavigationItemTranslation::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function label(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $fallback = (string) config('app.fallback_locale');
        $rows = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->get();
        $label = $rows->firstWhere('locale', $locale)?->label
            ?? $rows->firstWhere('locale', $fallback)?->label;

        if (filled($label)) {
            return $label;
        }

        $keys = [
            'home' => 'ui.nav.home',
            'about' => 'ui.nav.about',
            'divisions.index' => 'ui.nav.divisions',
            'projects.index' => 'ui.nav.projects',
            'contact' => 'ui.nav.contact',
        ];

        $key = $keys[$this->route_name] ?? null;

        return $key ? __($key) : '';
    }
}
