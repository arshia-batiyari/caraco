<?php

namespace App\Support;

/**
 * Stand-in photographs for public slots that have no uploaded file.
 * An admin upload replaces the stand-in.
 */
final class PreviewMedia
{
    /** @var array<string, string> */
    private const DIVISIONS = [
        'tourism' => 'division-tourism.jpg',
        'real-estate' => 'division-real-estate.jpg',
        'commercial' => 'division-commercial.jpg',
        'agriculture' => 'division-agriculture.jpg',
        'engineering' => 'division-engineering.jpg',
        'legal' => 'division-legal.jpg',
        'trading' => 'division-trading.jpg',
        'construction' => 'division-construction.jpg',
        'social' => 'division-social.jpg',
    ];

    public static function stage(): string
    {
        return asset('media/preview/stage-holding.jpg');
    }

    public static function hero(?string $stored): string
    {
        return ImageStore::url($stored) ?: asset('media/preview/hero-holding.jpg');
    }

    public static function division(?string $stored, string $key): ?string
    {
        $uploaded = ImageStore::url($stored);
        if ($uploaded !== null) {
            return $uploaded;
        }

        $file = self::DIVISIONS[$key] ?? null;

        return $file !== null ? asset('media/preview/'.$file) : null;
    }
}
