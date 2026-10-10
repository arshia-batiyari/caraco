<?php

use App\Support\Jalali;
use Carbon\CarbonInterface;

if (! function_exists('jdate')) {
    /**
     * تبدیل و فرمت تاریخ میلادی به شمسی — برای استفاده مستقیم تو ویوها.
     *
     * مثال‌ها:
     *   jdate($order->created_at)                  → ۱۴۰۴/۰۶/۱۴
     *   jdate($order->created_at, 'Y/m/d H:i')      → ۱۴۰۴/۰۶/۱۴ ۱۰:۳۲
     *   jdate($booth->approved_at, 'd F Y')         → ۱۴ شهریور ۱۴۰۴
     */
    function jdate(?CarbonInterface $date, string $format = 'Y/m/d'): string
    {
        return Jalali::format($date, $format);
    }
}
