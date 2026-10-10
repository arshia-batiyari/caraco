<?php

namespace Tests\Unit;

use App\Models\Tour;
use App\Support\TourPrice;
use Tests\TestCase;

class TourPriceTest extends TestCase
{
    public function test_percent_and_fixed_discounts_stay_in_the_same_currency(): void
    {
        $percent = new Tour([
            'base_price' => '200.00',
            'currency' => 'EUR',
            'discount_percent' => 10,
        ]);
        $this->assertSame('180.00', TourPrice::effectiveAmount($percent));
        $this->assertTrue(TourPrice::showsPercent($percent));

        $fixed = new Tour([
            'base_price' => '200.00',
            'currency' => 'EUR',
            'discount_price' => '150.00',
        ]);
        $this->assertSame('150.00', TourPrice::effectiveAmount($fixed));
        $this->assertFalse(TourPrice::showsPercent($fixed));

        $scheduled = new Tour([
            'base_price' => '200.00',
            'discount_percent' => 10,
            'discount_starts_at' => now()->addDay()->toDateString(),
        ]);
        $this->assertFalse(TourPrice::active($scheduled));
        $this->assertSame('200.00', TourPrice::effectiveAmount($scheduled));
    }
}
