<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Settlement extends Model
{
    protected $fillable = [
        'booth_id', 'period_start', 'period_end',
        'total_sales', 'commission_amount', 'payable_amount',
        'status', 'payment_reference', 'paid_by', 'paid_at', 'note',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function booth(): BelongsTo
    {
        return $this->belongsTo(Booth::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paidByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /** پرداخت‌شده کردن تسویه + ثبت برداشت معادلش در کیف پول (در یک تراکنش) */
    public function markPaid(User $admin, string $paymentReference): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($admin, $paymentReference) {
            if ($this->status === 'paid') {
                return;
            }

            $this->update([
                'status' => 'paid',
                'paid_by' => $admin->id,
                'payment_reference' => $paymentReference,
                'paid_at' => now(),
            ]);

            app(\App\Services\WalletService::class)->recordPayout($this, $admin);
        });
    }
}
