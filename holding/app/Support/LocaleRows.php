<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class LocaleRows
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $input
     * @param  list<string>  $columns
     */
    public static function sync(string $modelClass, array $input, array $columns): void
    {
        foreach (array_keys(config('karako.locales')) as $locale) {
            $row = is_array($input[$locale] ?? null) ? $input[$locale] : [];
            $payload = [];
            $blank = true;
            foreach ($columns as $column) {
                $payload[$column] = LocaleText::blank(isset($row[$column]) ? (string) $row[$column] : null);
                if ($payload[$column] !== null) {
                    $blank = false;
                }
            }

            $existing = $modelClass::query()->where('locale', $locale)->first();
            if ($blank) {
                $existing?->delete();

                continue;
            }

            $modelClass::query()->updateOrCreate(['locale' => $locale], $payload);
        }
    }
}
