<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

class SecureUrls
{
    public static function apply(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
            config(['session.secure' => true]);
        }
    }
}
