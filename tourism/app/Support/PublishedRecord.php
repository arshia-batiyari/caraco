<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class PublishedRecord
{
    /**
     * @param  class-string<Model>  $class
     * @param  list<string>  $with
     * @return array{0: Model, 1: Model}
     */
    public static function find(string $class, string $slug, string $label, array $with = []): array
    {
        $record = $class::query()
            ->published()
            ->whereHas('translations', fn ($query) => $query->where('locale', app()->getLocale())->where('slug', $slug))
            ->with(array_merge(['translations'], $with))
            ->first();
        abort_if($record === null, 404);
        $translation = $record->translations->firstWhere('locale', app()->getLocale());
        abort_if($translation === null || ! filled($translation->{$label}) || ! filled($translation->slug ?? null), 404);

        return [$record, $translation];
    }
}
