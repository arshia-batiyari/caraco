<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Booth;

/**
 * محاسبه هزینه و زمان تقریبی ارسال، مخصوص یک سایت سراسری (کل ایران).
 *
 * چون هر غرفه ممکنه تو هر استانی باشه و مشتری هم از هر استان دیگه‌ای سفارش بده،
 * هزینه بر اساس «منطقه» مبدأ-مقصد + وزن محموله محاسبه می‌شه، نه یه عدد ثابت:
 *
 *   - همون شهر (مبدأ و مقصد یکی)   → پیک موتوری/اسنپ‌باکس، ارزان و سریع
 *   - همون استان، شهر متفاوت       → تیپاکس درون‌استانی
 *   - استان‌های دیگر                → پست پیشتاز جمهوری اسلامی ایران (تعرفه وزنی)
 *
 * نرخ‌ها فرضی و قابل تنظیم‌اند — بعداً با تعرفه واقعی قرارداد با پست/تیپاکس جایگزین کنید.
 */
class ShippingService
{
    protected const BASE_RATES = [
        'same_city'     => 25000,  // تا ۱ کیلوگرم
        'same_province' => 40000,
        'nationwide'     => 60000,
    ];

    /** هزینه هر کیلوگرم اضافه، بعد از ۱ کیلوی اول */
    protected const PER_KG_RATE = [
        'same_city'     => 8000,
        'same_province' => 15000,
        'nationwide'     => 25000,
    ];

    protected const CARRIER = [
        'same_city'     => 'پیک موتوری درون‌شهری',
        'same_province' => 'تیپاکس (درون‌استانی)',
        'nationwide'     => 'پست پیشتاز جمهوری اسلامی ایران',
    ];

    protected const ESTIMATED_DAYS = [
        'same_city'     => 'همان روز تا ۱ روز کاری',
        'same_province' => '۱ تا ۲ روز کاری',
        'nationwide'     => '۲ تا ۵ روز کاری (بسته به فاصله استان)',
    ];

    /**
     * @return array{zone:string, carrier:string, cost:int, estimated_days:string}
     */
    public function estimate(Booth $booth, Address $address, int $totalWeightGrams): array
    {
        $zone = $this->resolveZone($booth, $address);
        $cost = $this->calculateCost($zone, $totalWeightGrams);

        return [
            'zone'           => $zone,
            'carrier'        => self::CARRIER[$zone],
            'cost'           => $cost,
            'estimated_days' => self::ESTIMATED_DAYS[$zone],
        ];
    }

    protected function resolveZone(Booth $booth, Address $address): string
    {
        $sameProvince = $this->normalize($booth->province) === $this->normalize($address->province);
        $sameCity = $sameProvince && $this->normalize($booth->city) === $this->normalize($address->city);

        return match (true) {
            $sameCity     => 'same_city',
            $sameProvince => 'same_province',
            default       => 'nationwide',
        };
    }

    protected function calculateCost(string $zone, int $weightGrams): int
    {
        $base = self::BASE_RATES[$zone];
        $extraKg = max(0, (int) ceil(($weightGrams - 1000) / 1000));

        return $base + $extraKg * self::PER_KG_RATE[$zone];
    }

    /** برای مقایسه نام استان/شهر بدون حساسیت به فاصله یا نیم‌فاصله */
    protected function normalize(?string $value): string
    {
        return trim(str_replace(['‌', ' '], '', (string) $value));
    }
}
