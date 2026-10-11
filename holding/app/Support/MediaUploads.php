<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaUploads
{
    public static function image(?string $current, ?UploadedFile $file, bool $remove, string $directory): ?string
    {
        if ($remove) {
            ImageStore::delete($current);
            $current = null;
        }

        if (! $file) {
            return $current;
        }

        $stored = ImageStore::store($file, $directory);
        ImageStore::delete($current);

        return $stored;
    }

    public static function video(?string $current, ?UploadedFile $file, bool $remove): ?string
    {
        if ($remove || $file) {
            ImageStore::delete($current);
            $current = null;
        }

        if (! $file) {
            return $current;
        }

        return $file->storeAs('videos', Str::uuid()->toString().'.mp4', 'public');
    }
}
