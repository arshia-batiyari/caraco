<?php

namespace App\Support;

use App\Models\Tour;
use Carbon\CarbonInterface;

class TourPrice
{
    public static function cents(mixed $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    public static function format(?string $amount, ?string $currency): ?string
    {
        if ($amount === null || $amount === '' || ! filled($currency)) {
            return null;
        }

        return number_format(self::cents($amount) / 100, 2, '.', ',').' '.strtoupper($currency);
    }

    public static function active(Tour $tour, ?CarbonInterface $on = null): bool
    {
        if ($tour->base_price === null || ! $tour->hasDiscountValue()) {
            return false;
        }

        $day = ($on ?? now())->copy()->startOfDay();
        if ($tour->discount_starts_at && $day->lt($tour->discount_starts_at->copy()->startOfDay())) {
            return false;
        }
        if ($tour->discount_ends_at && $day->gt($tour->discount_ends_at->copy()->startOfDay())) {
            return false;
        }

        return self::effectiveCents($tour, $day) < self::cents($tour->base_price);
    }

    public static function showsPercent(Tour $tour, ?CarbonInterface $on = null): bool
    {
        return self::active($tour, $on)
            && $tour->discount_percent !== null
            && $tour->discount_price === null;
    }

    public static function effectiveCents(Tour $tour, ?CarbonInterface $on = null): ?int
    {
        if ($tour->base_price === null) {
            return null;
        }

        $base = self::cents($tour->base_price);
        if (! self::windowOpen($tour, $on)) {
            return $base;
        }

        if ($tour->discount_price !== null && $tour->discount_percent === null) {
            $price = self::cents($tour->discount_price);

            return $price < $base ? $price : $base;
        }

        if ($tour->discount_percent !== null && $tour->discount_price === null) {
            return (int) round($base * (100 - (int) $tour->discount_percent) / 100);
        }

        return $base;
    }

    public static function effectiveAmount(Tour $tour): ?string
    {
        $cents = self::effectiveCents($tour);

        return $cents === null ? null : number_format($cents / 100, 2, '.', '');
    }

    public static function effectiveExpression(): string
    {
        return '(CASE
            WHEN base_price IS NOT NULL
                AND (discount_starts_at IS NULL OR discount_starts_at <= ?)
                AND (discount_ends_at IS NULL OR discount_ends_at >= ?)
                AND discount_price IS NOT NULL
                AND discount_percent IS NULL
                AND discount_price < base_price
            THEN discount_price
            WHEN base_price IS NOT NULL
                AND (discount_starts_at IS NULL OR discount_starts_at <= ?)
                AND (discount_ends_at IS NULL OR discount_ends_at >= ?)
                AND discount_percent IS NOT NULL
                AND discount_price IS NULL
            THEN ROUND(base_price * (100 - discount_percent) / 100, 2)
            ELSE base_price
        END)';
    }

    public static function betweenExpression(): string
    {
        $amount = 'CAST('.self::effectiveExpression().' AS DECIMAL(12,2))';

        return $amount.' BETWEEN CAST(? AS DECIMAL(12,2)) AND CAST(? AS DECIMAL(12,2))';
    }

    /**
     * @return list<string>
     */
    public static function expressionBindings(): array
    {
        $today = now()->toDateString();

        return [$today, $today, $today, $today];
    }

    private static function windowOpen(Tour $tour, ?CarbonInterface $on = null): bool
    {
        $day = ($on ?? now())->copy()->startOfDay();
        if ($tour->discount_starts_at && $day->lt($tour->discount_starts_at->copy()->startOfDay())) {
            return false;
        }
        if ($tour->discount_ends_at && $day->gt($tour->discount_ends_at->copy()->startOfDay())) {
            return false;
        }

        return true;
    }
}
