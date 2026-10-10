<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactTranslation extends Model
{
    protected $fillable = ['locale', 'address', 'hours', 'note'];

    public static function forLocale(?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();
        $fallback = (string) config('app.fallback_locale');
        $rows = static::query()->whereIn('locale', [$locale, $fallback])->get();

        return $rows->firstWhere('locale', $locale) ?? $rows->firstWhere('locale', $fallback);
    }
}
