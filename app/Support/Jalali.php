<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * تبدیل تاریخ میلادی به شمسی، بدون نیاز به نصب پکیج جداگانه.
 * الگوریتم تبدیل، همون الگوریتم استاندارد و شناخته‌شده‌ای‌ست که تو اکثر
 * پروژه‌های PHP فارسی (jdf.php و مشابهش) استفاده می‌شه.
 */
class Jalali
{
    protected const MONTH_NAMES = [
        1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
        5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
        9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
    ];

    protected const WEEKDAY_NAMES = [
        'Saturday' => 'شنبه', 'Sunday' => 'یکشنبه', 'Monday' => 'دوشنبه',
        'Tuesday' => 'سه‌شنبه', 'Wednesday' => 'چهارشنبه', 'Thursday' => 'پنجشنبه', 'Friday' => 'جمعه',
    ];

    /** @return array{0:int,1:int,2:int} [سال, ماه, روز] شمسی */
    public static function fromCarbon(CarbonInterface $date): array
    {
        return self::toJalali((int) $date->format('Y'), (int) $date->format('n'), (int) $date->format('j'));
    }

    public static function toJalali(int $gy, int $gm, int $gd): array
    {
        $gDaysInMonth = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = $gm > 2 ? $gy + 1 : $gy;

        $days = 355666
            + (365 * $gy)
            + intdiv($gy2 + 3, 4)
            - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400)
            + $gd
            + $gDaysInMonth[$gm - 1];

        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;

        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }

    /**
     * فرمت‌های پشتیبانی‌شده: Y (سال ۴رقمی)، y (۲رقمی)، m (ماه دورقمی)، n (ماه)،
     * d (روز دورقمی)، j (روز)، F (نام ماه فارسی)، l (نام روز هفته فارسی)، H، i، s
     */
    public static function format(?CarbonInterface $date, string $format = 'Y/m/d', bool $persianDigits = true): string
    {
        if (! $date) {
            return '';
        }

        [$jy, $jm, $jd] = self::fromCarbon($date);

        $replacements = [
            'Y' => (string) $jy,
            'y' => substr((string) $jy, -2),
            'm' => str_pad((string) $jm, 2, '0', STR_PAD_LEFT),
            'n' => (string) $jm,
            'd' => str_pad((string) $jd, 2, '0', STR_PAD_LEFT),
            'j' => (string) $jd,
            'F' => self::MONTH_NAMES[$jm],
            'l' => self::WEEKDAY_NAMES[$date->format('l')] ?? '',
            'H' => $date->format('H'),
            'i' => $date->format('i'),
            's' => $date->format('s'),
        ];

        // جایگزینی کاراکتر به کاراکتر تا مثلاً حرف "m" داخل بقیه متن (اگه بود) دست‌خورده نشه
        $out = '';
        foreach (str_split($format) as $char) {
            $out .= $replacements[$char] ?? $char;
        }

        return $persianDigits ? self::toPersianDigits($out) : $out;
    }

    public static function toPersianDigits(string $value): string
    {
        return str_replace(
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            $value
        );
    }
}
