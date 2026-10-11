<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class AlternateLinks
{
    /**
     * @return array<string, string>
     */
    public static function section(string $route): array
    {
        $links = [];
        foreach (array_keys(config('karako.locales')) as $locale) {
            $links[$locale] = route($route, ['locale' => $locale]);
        }

        return $links;
    }

    /**
     * @return array<string, string>
     */
    public static function record(Model $record, string $route, string $label): array
    {
        $links = [];
        foreach (array_keys(config('karako.locales')) as $locale) {
            $row = $record->translations->firstWhere('locale', $locale);
            $links[$locale] = $row && filled($row->{$label} ?? null) && filled($row->slug ?? null)
                ? route($route, ['locale' => $locale, 'slug' => $row->slug])
                : route('home', ['locale' => $locale]);
        }

        return $links;
    }
}
