<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class LocaleText
{
    public static function blank(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public static function slug(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^\p{L}\p{N}]+/u', '-', $value) ?? '';

        return trim($value, '-');
    }

    public static function paragraphs(?string $body): array
    {
        $body = trim((string) $body);
        if ($body === '') {
            return [];
        }

        $parts = preg_split("/\n\s*\n/u", $body) ?: [];

        return array_values(array_filter(array_map('trim', $parts)));
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  list<string>  $columns
     */
    public static function sync(Model $parent, array $input, array $columns, string $required): void
    {
        foreach (array_keys(config('karako.locales')) as $locale) {
            $row = is_array($input[$locale] ?? null) ? $input[$locale] : [];
            $payload = ['locale' => $locale];
            foreach ($columns as $column) {
                $payload[$column] = self::blank(isset($row[$column]) ? (string) $row[$column] : null);
            }

            $existing = $parent->translations()->where('locale', $locale)->first();
            if ($payload[$required] === null) {
                $existing?->delete();

                continue;
            }

            if (array_key_exists('slug', $payload) && $payload['slug'] === null) {
                $payload['slug'] = self::slug((string) $payload[$required]);
            }

            if (array_key_exists('slug', $payload)) {
                $payload['slug'] = self::uniqueSlug($parent, $locale, (string) $payload['slug'], $existing?->id);
            }

            $parent->translations()->updateOrCreate(['locale' => $locale], $payload);
        }
    }

    private static function uniqueSlug(Model $parent, string $locale, string $slug, ?int $ignoreId): string
    {
        $base = $slug !== '' ? $slug : 'page';
        $candidate = $base;
        $n = 2;
        $relation = $parent->translations();

        while ($relation->getRelated()->newQuery()
            ->where('locale', $locale)
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base.'-'.$n;
            $n++;
        }

        return $candidate;
    }
}
