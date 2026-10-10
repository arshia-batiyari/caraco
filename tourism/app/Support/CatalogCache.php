<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class CatalogCache
{
    public const KEY = 'tourism.catalog.filters';

    public static function flush(): void
    {
        Cache::forget(self::KEY);
    }
}
