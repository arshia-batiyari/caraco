<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * زبان‌های پشتیبانی‌شده سایت.
     */
    private const SUPPORTED = ['fa', 'en'];

    /**
     * اگر لینک "?lang=fa" یا "?lang=en" زده شده باشه، اون رو تو سشن ذخیره
     * می‌کنه تا تا وقتی کاربر عوضش نکرده، تو کل سایت (حتی بین صفحات) بمونه.
     * اگه چیزی تو سشن نبود، از APP_LOCALE پیش‌فرض استفاده می‌شه.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query('lang');

        if (is_string($requested) && in_array($requested, self::SUPPORTED, true)) {
            session(['locale' => $requested]);
        }

        $locale = session('locale');

        if (! is_string($locale) || ! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
