<?php

namespace App\Support;

use App\Models\Attraction;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\MedicalProvider;
use App\Models\Tour;

/**
 * Stand-in photographs for published records that have no uploaded file.
 * Matched on the record's own names. An admin upload replaces the stand-in.
 */
final class PreviewMedia
{
    public static function hero(?string $stored): string
    {
        return ImageStore::url($stored) ?: asset('media/preview/tourism-hero.jpg');
    }

    public static function destination(Destination $record): ?string
    {
        return self::match($record->image_path, self::labels($record, 'name'), [
            'Yazd' => 'destination-yazd.jpg',
            'یزد' => 'destination-yazd.jpg',
            'Sample country' => 'destination-country.jpg',
            'کشور نمونه' => 'destination-country.jpg',
            'Sample city' => 'destination-city.jpg',
            'شهر نمونه' => 'destination-city.jpg',
        ]);
    }

    public static function tour(Tour $record): ?string
    {
        return self::match($record->cover_path, self::labels($record, 'title'), [
            'Yazd lanes' => 'tour-yazd-lanes.jpg',
            'کوچه‌های یزد' => 'tour-yazd-lanes.jpg',
        ]);
    }

    public static function hotel(Hotel $record): ?string
    {
        $first = $record->images->first();
        if ($first !== null && filled($first->path)) {
            return ImageStore::url($first->path);
        }

        return self::match(null, self::labels($record, 'name'), [
            'Lane house' => 'hotel-lane-house.jpg',
        ]);
    }

    public static function attraction(Attraction $record): ?string
    {
        return self::match($record->cover_path, self::labels($record, 'title'), [
            'Unverified garden' => 'attraction-garden.jpg',
        ]);
    }

    public static function provider(MedicalProvider $record): ?string
    {
        return self::match($record->cover_path, self::labels($record, 'name'), [
            'North clinic' => 'provider-building.jpg',
            'درمانگاه شمال' => 'provider-building.jpg',
        ]);
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<string, string>  $files
     */
    private static function match(?string $stored, array $labels, array $files): ?string
    {
        $uploaded = ImageStore::url($stored);
        if ($uploaded !== null) {
            return $uploaded;
        }

        foreach ($labels as $label) {
            if (isset($files[$label])) {
                return asset('media/preview/'.$files[$label]);
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private static function labels(object $record, string $field): array
    {
        $labels = [];
        foreach ($record->translations as $row) {
            $value = $row->{$field} ?? null;
            if (filled($value)) {
                $labels[] = (string) $value;
            }
        }

        return $labels;
    }
}
