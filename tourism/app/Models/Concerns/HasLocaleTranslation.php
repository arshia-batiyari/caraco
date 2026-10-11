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
        $label = $this->translationLabel();
        $locale = app()->getLocale();
        $rows = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->get();
        $current = $rows->firstWhere('locale', $locale);

        if ($current && filled($current->{$label} ?? null)) {
            return $current;
        }

        $fallback = $this->translated();
        if ($fallback && filled($fallback->{$label} ?? null)) {
            return $fallback;
        }

        return null;
    }

    public function publicUrl(string $route): ?string
    {
        $translation = $this->publicTranslation();
        if (! $translation || ! filled($translation->{$this->translationLabel()} ?? null)) {
            return null;
        }

        if (! filled($translation->slug ?? null)) {
            return null;
        }

        return route($route, [
            'locale' => $translation->locale,
            'slug' => $translation->slug,
        ]);
    }

    protected function translationLabel(): string
    {
        return 'title';
    }
}
