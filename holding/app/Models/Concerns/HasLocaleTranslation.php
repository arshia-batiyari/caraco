<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasLocaleTranslation
{
    public function translated(?string $locale = null): ?Model
    {
        $locale = $locale ?? app()->getLocale();
        $fallback = (string) config('app.fallback_locale');
        $rows = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->get();

        return $rows->firstWhere('locale', $locale)
            ?? $rows->firstWhere('locale', $fallback);
    }

    public function publicTranslation(): ?Model
    {
        $locale = app()->getLocale();
        $rows = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->get();
        $current = $rows->firstWhere('locale', $locale);

        if ($current && filled($current->title ?? null)) {
            return $current;
        }

        return $this->translated();
    }

    public function publicUrl(string $route): ?string
    {
        $translation = $this->publicTranslation();
        if (! $translation || ! filled($translation->title ?? null)) {
            return null;
        }

        $params = ['locale' => $translation->locale];
        if ($route !== 'about') {
            if (! filled($translation->slug ?? null)) {
                return null;
            }
            $params['slug'] = $translation->slug;
        }

        return route($route, $params);
    }
}
