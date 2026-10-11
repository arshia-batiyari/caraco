<?php

namespace App\Support;

class HttpUrl
{
    public static function valid(?string $url): bool
    {
        $url = trim((string) $url);

        return $url !== ''
            && preg_match('#^https?://#i', $url) === 1
            && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
