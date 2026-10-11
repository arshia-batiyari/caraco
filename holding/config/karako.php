<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site identity
    |--------------------------------------------------------------------------
    |
    | Holding and tourism are separate applications. This key identifies which
    | one is running. Public URLs come from the environment so no domain is
    | hardcoded.
    |
    */

    'site' => 'holding',

    'public_url' => env('APP_URL', 'http://localhost'),

    'related_url' => env('TOURISM_URL'),

    // Absolute web root when the host cannot point the domain at public/.
    'public_path' => env('KARAKO_PUBLIC_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | Persian and Arabic are RTL. English and Russian are LTR. Content
    | translations are added in later phases; this list is the contract.
    |
    */

    'locales' => [
        'fa' => ['name' => 'Persian', 'native' => 'فارسی', 'dir' => 'rtl'],
        'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr'],
        'ru' => ['name' => 'Russian', 'native' => 'Русский', 'dir' => 'ltr'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'dir' => 'rtl'],
    ],

];
