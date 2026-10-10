<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageTranslation extends Model
{
    protected $fillable = [
        'locale',
        'hero_eyebrow',
        'hero_title',
        'hero_lede',
        'intro_heading',
        'intro_body',
        'divisions_heading',
        'approach_heading',
        'approach_body',
        'projects_heading',
        'cta_heading',
        'cta_text',
        'meta_title',
        'meta_description',
        'canonical_url',
    ];

    public static function forLocale(?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();
        $fallback = (string) config('app.fallback_locale');
        $rows = static::query()->whereIn('locale', [$locale, $fallback])->get();

        return $rows->firstWhere('locale', $locale) ?? $rows->firstWhere('locale', $fallback);
    }
}
