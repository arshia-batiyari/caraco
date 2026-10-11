<?php

namespace App\Http\Controllers;

use App\Support\HttpUrl;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class MapTileController extends Controller
{
    public function __invoke(int $z, int $x, int $y): Response
    {
        $template = config('karako.map.tiles');
        if (! is_string($template) || ! str_contains($template, '{z}') || ! str_contains($template, '{x}') || ! str_contains($template, '{y}')) {
            abort(404);
        }

        if ($z < 0 || $z > 18 || $x < 0 || $y < 0 || $x >= 2 ** $z || $y >= 2 ** $z) {
            abort(404);
        }

        $url = str_replace(['{z}', '{x}', '{y}'], [(string) $z, (string) $x, (string) $y], $template);
        $configuredHost = parse_url($template, PHP_URL_HOST);
        $host = parse_url($url, PHP_URL_HOST);
        if (! HttpUrl::valid($url) || ! is_string($configuredHost) || $host !== $configuredHost) {
            abort(404);
        }

        $response = Http::timeout(4)->get($url);
        $type = (string) $response->header('Content-Type');
        if (! $response->successful() || ! str_starts_with($type, 'image/')) {
            abort(404);
        }

        return response($response->body(), 200, [
            'Content-Type' => $type,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
