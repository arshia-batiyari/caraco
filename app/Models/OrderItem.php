<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'booth_id',
        'product_name', 'unit_price', 'quantity', 'line_total',
        'commission_percent', 'commission_amount', 'booth_payout_amount',
        'item_status', 'shipment_id', 'settlement_id',
    ];

    protected function casts(): array
    {
        return [
            'product_name' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function booth(): BelongsTo
    {
        return $this->belongsTo(Booth::class);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(Settlement::class);
    }

    /**
     * محاسبه کمیسیون بر اساس درصد غرفه و ساخت مقادیر نهایی.
     * فراخوانی می‌شود قبل از ذخیره، هنگام ثبت سفارش.
     */
    public static function calculateAmounts(int $unitPrice, int $quantity, float $commissionPercent): array
    {
        $lineTotal = $unitPrice * $quantity;
        $commissionAmount = (int) round($lineTotal * $commissionPercent / 100);
        $boothPayout = $lineTotal - $commissionAmount;

        return [
            'line_total' => $lineTotal,
            'commission_amount' => $commissionAmount,
            'booth_payout_amount' => $boothPayout,
        ];
    }
}
