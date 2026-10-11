<?php

namespace App\Support;

class LocaleUrl
{
    public static function forLocale(string $locale): string
    {
        $route = request()->route();
        $name = $route?->getName();
        $params = $route?->parameters() ?? [];
        $params['locale'] = $locale;

        if (is_string($name) && $name !== '') {
            return route($name, $params);
        }

        return url('/'.$locale);
    }
}
