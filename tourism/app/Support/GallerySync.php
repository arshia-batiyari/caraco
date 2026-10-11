<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class GallerySync
{
    public static function replace(?string $current, ?UploadedFile $file, bool $remove, string $directory, string $field): ?string
    {
        if ($remove) {
            ImageStore::delete($current);
            $current = null;
        }

        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return $current;
        }

        try {
            $path = ImageStore::store($file, $directory);
        } catch (RuntimeException $exception) {
            if ($exception instanceof \Illuminate\Database\QueryException) {
                throw $exception;
            }
            throw ValidationException::withMessages([
                $field => __('admin.validation.image'),
            ]);
        }

        ImageStore::delete($current);

        return $path;
    }

    public static function images(Request $request, Model $record, string $directory): void
    {
        $remove = array_map('intval', (array) $request->input('remove_images', []));
        $record->images()->whereIn('id', $remove)->get()->each(function (Model $image) {
            ImageStore::delete($image->path);
            $image->delete();
        });

        foreach ((array) $request->input('images_meta', []) as $id => $row) {
            $image = $record->images()->whereKey($id)->first();
            if (! $image || ! is_array($row)) {
                continue;
            }
            self::syncCaption($image, $row['translations'] ?? []);
        }

        try {
            $position = (int) $record->images()->max('position') + 1;
            foreach ($request->file('images', []) as $file) {
                if (! $file instanceof UploadedFile || ! $file->isValid()) {
                    continue;
                }
                $record->images()->create([
                    'path' => ImageStore::store($file, $directory),
                    'position' => $position,
                ]);
                $position++;
            }
        } catch (RuntimeException $exception) {
            if ($exception instanceof \Illuminate\Database\QueryException) {
                throw $exception;
            }
            throw ValidationException::withMessages([
                'images' => __('admin.validation.image'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $translations
     */
    public static function syncCaption(Model $image, array $translations): void
    {
        foreach (array_keys(config('karako.locales')) as $locale) {
            $row = is_array($translations[$locale] ?? null) ? $translations[$locale] : [];
            $alt = LocaleText::blank(isset($row['alt']) ? (string) $row['alt'] : null);
            $caption = LocaleText::blank(isset($row['caption']) ? (string) $row['caption'] : null);
            $existing = $image->translations()->where('locale', $locale)->first();
            if ($alt === null && $caption === null) {
                $existing?->delete();

                continue;
            }
            $image->translations()->updateOrCreate(['locale' => $locale], [
                'alt' => $alt,
                'caption' => $caption,
            ]);
        }
    }
}
