<?php

namespace App\Support;

use Illuminate\Support\Collection;

class RouteMap
{
    /**
     * @param  Collection<int, mixed>  $points
     */
    public static function svg(Collection $points): ?string
    {
        $placed = $points->filter(function ($point) {
            return $point->latitude !== null && $point->longitude !== null;
        })->values();

        if ($placed->isEmpty()) {
            return null;
        }

        $lats = $placed->map(fn ($point) => (float) $point->latitude);
        $lngs = $placed->map(fn ($point) => (float) $point->longitude);
        $minLat = (float) $lats->min();
        $maxLat = (float) $lats->max();
        $minLng = (float) $lngs->min();
        $maxLng = (float) $lngs->max();
        $latSpan = max($maxLat - $minLat, 0.000001);
        $lngSpan = max($maxLng - $minLng, 0.000001);

        $width = 640;
        $height = 360;
        $pad = 36;
        $coords = [];
        foreach ($placed as $point) {
            $x = $pad + (((float) $point->longitude - $minLng) / $lngSpan) * ($width - ($pad * 2));
            $y = $pad + (($maxLat - (float) $point->latitude) / $latSpan) * ($height - ($pad * 2));
            if ($latSpan <= 0.000001 && $lngSpan <= 0.000001) {
                $x = $width / 2;
                $y = $height / 2;
            }
            $coords[] = [round($x, 1), round($y, 1), $point];
        }

        $polyline = '';
        if (count($coords) > 1) {
            $polyline = '<polyline fill="none" stroke="#7a5c32" stroke-width="2" points="'.
                implode(' ', array_map(fn (array $row) => $row[0].','.$row[1], $coords)).
                '" />';
        }

        $marks = '';
        foreach ($coords as $index => $row) {
            $name = htmlspecialchars((string) ($row[2]->publicTranslation()?->name ?: ($index + 1)), ENT_QUOTES, 'UTF-8');
            $marks .= '<g><circle cx="'.$row[0].'" cy="'.$row[1].'" r="5" fill="#141210" />';
            $marks .= '<text x="'.($row[0] + 8).'" y="'.($row[1] - 8).'" fill="#141210" font-size="13" font-family="Source Sans 3, Vazirmatn, sans-serif">'.$name.'</text></g>';
        }

        return '<svg viewBox="0 0 '.$width.' '.$height.'" role="img" class="k-route-svg">'
            .'<rect width="'.$width.'" height="'.$height.'" fill="#f4efe6" />'
            .'<title>'.htmlspecialchars(__('ui.tours.route'), ENT_QUOTES, 'UTF-8').'</title>'
            .$polyline.$marks.'</svg>';
    }

    /**
     * @param  Collection<int, mixed>  $points
     * @return list<array{lat: float, lng: float, name: string}>
     */
    public static function coordinates(Collection $points): array
    {
        $rows = [];
        foreach ($points as $point) {
            if ($point->latitude === null || $point->longitude === null) {
                continue;
            }
            $rows[] = [
                'lat' => (float) $point->latitude,
                'lng' => (float) $point->longitude,
                'name' => (string) ($point->publicTranslation()?->name ?? ''),
            ];
        }

        return $rows;
    }
}
