<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * یک سند حسابداری در کیف پول غرفه (نگاه کنید به مایگریشن create_wallet_transactions_table).
 * مبلغ مثبت = واریز به کیف پول، مبلغ منفی = کسر/برداشت.
 */
class WalletTransaction extends Model
{
    public const TYPE_SALE = 'sale';
    public const TYPE_REVERSAL = 'reversal';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_PAYOUT = 'payout';

    protected $fillable = [
        'booth_id', 'type', 'amount', 'gross_amount', 'commission_amount', 'share_percent',
        'order_id', 'order_item_id', 'settlement_id', 'description', 'reference', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'gross_amount' => 'integer',
            'commission_amount' => 'integer',
            'share_percent' => 'decimal:2',
        ];
    }

    public function booth(): BelongsTo
    {
        return $this->belongsTo(Booth::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(Settlement::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return __('wallet.type_'.$this->type);
    }

    public function isCredit(): bool
    {
        return $this->amount > 0;
    }
}
