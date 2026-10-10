<?php

namespace App\Services;

use App\Models\Booth;
use App\Models\Order;
use App\Models\Settlement;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

/**
 * حسابداری کیف پول غرفه‌ها.
 *
 * قانون کلی: هیچ‌جای برنامه موجودی رو مستقیم تغییر نمی‌ده؛ فقط سند (WalletTransaction)
 * ثبت می‌شه و موجودی همیشه از جمع اسناد محاسبه می‌شه. همه‌ی متدهای ثبت
 * idempotent هستن (دوبار فراخوانی = دوبار ثبت نمی‌شه).
 */
class WalletService
{
    /**
     * بعد از تأیید پرداخت سفارش: سهم هر غرفه از هر آیتم به کیف پولش واریز می‌شه.
     * مبلغ‌ها از خود آیتم سفارش خونده می‌شن (نه از درصد فعلی غرفه) چون درصد
     * لحظه‌ی ثبت سفارش روی آیتم قفل شده و تغییر بعدی درصد نباید اثر بذاره.
     *
     * @return int تعداد سندهای جدیدِ ثبت‌شده
     */
    public function creditPaidOrder(Order $order): int
    {
        return DB::transaction(function () use ($order) {
            $created = 0;

            foreach ($order->items()->get() as $item) {
                $exists = WalletTransaction::where('order_item_id', $item->id)
                    ->where('type', WalletTransaction::TYPE_SALE)->exists();

                if ($exists) {
                    continue;
                }

                WalletTransaction::create([
                    'booth_id' => $item->booth_id,
                    'type' => WalletTransaction::TYPE_SALE,
                    'amount' => (int) $item->booth_payout_amount,
                    'gross_amount' => (int) $item->line_total,
                    'commission_amount' => (int) $item->commission_amount,
                    'share_percent' => round(100 - (float) $item->commission_percent, 2),
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                ]);
                $created++;
            }

            return $created;
        });
    }

    /**
     * رد/لغو پرداخت بعد از اینکه سهم غرفه‌ها واریز شده بود: سند برگشت (منفی) ثبت می‌شه.
     * اگه سهم قبلاً تسویه شده باشه، این مبلغ منفی از تسویه‌ی بعدی کم می‌شه.
     */
    public function reverseOrder(Order $order, ?User $by = null, ?string $reason = null): int
    {
        return DB::transaction(function () use ($order, $by, $reason) {
            $count = 0;

            $sales = WalletTransaction::where('order_id', $order->id)
                ->where('type', WalletTransaction::TYPE_SALE)->get();

            foreach ($sales as $sale) {
                $already = WalletTransaction::where('order_item_id', $sale->order_item_id)
                    ->where('type', WalletTransaction::TYPE_REVERSAL)->exists();

                if ($already) {
                    continue;
                }

                WalletTransaction::create([
                    'booth_id' => $sale->booth_id,
                    'type' => WalletTransaction::TYPE_REVERSAL,
                    'amount' => -1 * $sale->amount,
                    'gross_amount' => -1 * (int) $sale->gross_amount,
                    'commission_amount' => -1 * (int) $sale->commission_amount,
                    'share_percent' => $sale->share_percent,
                    'order_id' => $order->id,
                    'order_item_id' => $sale->order_item_id,
                    'description' => $reason,
                    'created_by' => $by?->id,
                ]);
                $count++;
            }

            return $count;
        });
    }

    /** سند اصلاحی دستی ادمین: مبلغ مثبت = بستانکار، منفی = بدهکار. شرح اجباریه. */
    public function adjust(Booth $booth, int $amount, string $description, User $admin): WalletTransaction
    {
        if ($amount === 0) {
            throw new \InvalidArgumentException('مبلغ سند اصلاحی نمی‌تواند صفر باشد.');
        }

        return WalletTransaction::create([
            'booth_id' => $booth->id,
            'type' => WalletTransaction::TYPE_ADJUSTMENT,
            'amount' => $amount,
            'description' => $description,
            'created_by' => $admin->id,
        ]);
    }

    /** وقتی ادمین تسویه رو «پرداخت‌شده» می‌کنه: برداشت معادل از کیف پول ثبت می‌شه. */
    public function recordPayout(Settlement $settlement, User $admin): ?WalletTransaction
    {
        $exists = WalletTransaction::where('settlement_id', $settlement->id)
            ->where('type', WalletTransaction::TYPE_PAYOUT)->exists();

        if ($exists || $settlement->payable_amount <= 0) {
            return null;
        }

        return WalletTransaction::create([
            'booth_id' => $settlement->booth_id,
            'type' => WalletTransaction::TYPE_PAYOUT,
            'amount' => -1 * (int) $settlement->payable_amount,
            'settlement_id' => $settlement->id,
            'reference' => $settlement->payment_reference,
            'created_by' => $admin->id,
        ]);
    }

    /**
     * خلاصه‌ی حساب یک غرفه.
     *
     * balance        کل مانده‌ی کیف پول (جمع همه‌ی اسناد)
     * in_settlement  بخشی از مانده که تو یه تسویه‌ی «در انتظار واریز» قفل شده
     * available      بقیه‌ی مانده که هنوز تو هیچ تسویه‌ای نیست (قابل تسویه)
     */
    public function summary(Booth $booth): array
    {
        $base = WalletTransaction::where('booth_id', $booth->id);

        $balance = (int) (clone $base)->sum('amount');

        $inSettlement = (int) (clone $base)
            ->where('type', '!=', WalletTransaction::TYPE_PAYOUT)
            ->whereIn('settlement_id', Settlement::where('booth_id', $booth->id)->where('status', 'pending')->select('id'))
            ->sum('amount');

        $sales = (clone $base)->whereIn('type', [WalletTransaction::TYPE_SALE, WalletTransaction::TYPE_REVERSAL]);

        return [
            'balance' => $balance,
            'in_settlement' => $inSettlement,
            'available' => $balance - $inSettlement,
            'gross_sales' => (int) (clone $sales)->sum('gross_amount'),
            'commission_total' => (int) (clone $sales)->sum('commission_amount'),
            'net_earned' => (int) (clone $sales)->sum('amount'),
            'adjustments' => (int) (clone $base)->where('type', WalletTransaction::TYPE_ADJUSTMENT)->sum('amount'),
            'paid_out' => -1 * (int) (clone $base)->where('type', WalletTransaction::TYPE_PAYOUT)->sum('amount'),
        ];
    }
}
