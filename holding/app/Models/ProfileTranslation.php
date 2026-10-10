<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileTranslation extends Model
{
    protected $fillable = ['locale', 'summary'];

    public static function forLocale(?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();
        $fallback = (string) config('app.fallback_locale');
        $rows = static::query()->whereIn('locale', [$locale, $fallback])->get();
        $current = $rows->firstWhere('locale', $locale);
        if ($current && filled($current->summary)) {
            return $current;
        }

        $english = $rows->firstWhere('locale', $fallback);

        return $english && filled($english->summary) ? $english : null;
    }
}
