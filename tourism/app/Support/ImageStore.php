<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageStore
{
    public static function store(UploadedFile $file, string $directory): string
    {
        $contents = file_get_contents($file->getPathname());
        if ($contents === false) {
            throw new RuntimeException('The image could not be read.');
        }

        $source = @imagecreatefromstring($contents);
        if ($source === false) {
            throw new RuntimeException('The image could not be processed.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $max = 1800;
        $targetWidth = $width;
        $targetHeight = $height;
        if ($width > $max) {
            $targetWidth = $max;
            $targetHeight = (int) round($height * ($max / $width));
        }

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        ob_start();
        imagejpeg($canvas, null, 82);
        $jpeg = ob_get_clean();
        imagedestroy($source);
        imagedestroy($canvas);

        if (! is_string($jpeg) || $jpeg === '') {
            throw new RuntimeException('The image could not be saved.');
        }

        $id = Str::uuid()->toString();
        $directory = trim($directory, '/');
        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'bin';
        Storage::disk('public')->put($directory.'/'.$id.'-source.'.$extension, $contents);

        $path = $directory.'/'.$id.'.jpg';
        Storage::disk('public')->put($path, $jpeg);

        return $path;
    }

    public static function delete(?string $path): void
    {
        if (! filled($path)) {
            return;
        }

        $disk = Storage::disk('public');
        if ($disk->exists($path)) {
            $disk->delete($path);
        }

        $directory = trim(str_replace('\\', '/', dirname($path)), '.');
        $name = pathinfo($path, PATHINFO_FILENAME);
        foreach ($disk->files($directory) as $file) {
            if (pathinfo($file, PATHINFO_FILENAME) === $name.'-source') {
                $disk->delete($file);
            }
        }
    }

    public static function url(?string $path): ?string
    {
        return filled($path) ? Storage::disk('public')->url($path) : null;
    }
}
