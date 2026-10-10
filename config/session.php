<?php

use Illuminate\Support\Str;

return [

    'driver' => env('SESSION_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    |
    | پیش‌فرض قبلی ۱۲۰ دقیقه بود که باعث می‌شد سبد خرید مهمان‌ها (که رو سشن
    | ذخیره می‌شه) با کمی بی‌کاری خالی بشه، و کاربرهای لاگین‌کرده هم زودتر از
    | حد انتظار خارج بشن. لاگین با «مرا به خاطر بسپار» (که همیشه فعاله، هم تو
    | فرم ایمیل/رمز هم تو ورود با کد پیامکی) جدا از این، ورود رو تا ۵ سال حفظ
    | می‌کنه؛ این عدد فقط خود سشن (و سبد خرید مهمان) رو طولانی‌تر نگه می‌داره.
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 10080),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    'encrypt' => env('SESSION_ENCRYPT', false),

    'files' => storage_path('framework/sessions'),

    'connection' => env('SESSION_CONNECTION'),

    'table' => env('SESSION_TABLE', 'sessions'),

    'store' => env('SESSION_STORE'),

    'lottery' => [2, 100],

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),

    'path' => env('SESSION_PATH', '/'),

    'domain' => env('SESSION_DOMAIN'),

    'secure' => env('SESSION_SECURE_COOKIE'),

    'http_only' => env('SESSION_HTTP_ONLY', true),

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];
