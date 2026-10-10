<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'address_id',
        'items_amount', 'shipping_amount', 'shipping_breakdown', 'total_amount',
        'status', 'payment_status', 'payment_gateway',
        'payment_authority', 'payment_ref_id', 'paid_at',
        'payment_receipt_path', 'payment_submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'payment_submitted_at' => 'datetime',
            'shipping_breakdown' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getPaymentReceiptUrlAttribute(): ?string
    {
        return $this->payment_receipt_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->payment_receipt_path)
            : null;
    }

    /**
     * تولید شماره سفارش یکتا مثل MYD-140406-0001 (بخش تاریخ به شمسی).
     */
    public static function generateOrderNumber(): string
    {
        $datePart = \App\Support\Jalali::format(now(), 'ymd', persianDigits: false);
        $sequence = str_pad((string) (self::whereDate('created_at', now())->count() + 1), 4, '0', STR_PAD_LEFT);

        return "MYD-{$datePart}-{$sequence}";
    }
}
