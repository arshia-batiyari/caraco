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
        'destinations_heading',
        'categories_heading',
        'popular_heading',
        'discounted_heading',
        'domestic_heading',
        'international_heading',
        'experiences_heading',
        'medical_heading',
        'medical_body',
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

        return static::query()->where('locale', $locale)->first()
            ?? static::query()->where('locale', $fallback)->first();
    }
}
