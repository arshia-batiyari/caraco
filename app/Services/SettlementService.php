<?php

namespace App\Services;

use App\Models\Booth;
use App\Models\OrderItem;
use App\Models\Settlement;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * محاسبه و ساخت دسته‌های تسویه‌حساب برای غرفه‌داران.
 * معمولاً به‌صورت دوره‌ای (مثلاً هر ۱۵ روز) توسط ادمین یا یک Scheduled Command اجرا می‌شود.
 */
class SettlementService
{
    /**
     * برای یک غرفه، همه‌ی اسناد تسویه‌نشده‌ی کیف پول (سهم فروش‌ها، برگشتی‌ها و
     * اسناد اصلاحی) تا پایان بازه رو در یک رکورد Settlement جمع می‌کند.
     * مبلغ قابل واریز = جمع جبری اسناد؛ پس برگشتی و جریمه‌ها خودکار کم می‌شن.
     * اگه جمع صفر یا منفی باشه تسویه‌ای ساخته نمی‌شه و اسناد برای دوره‌ی بعد می‌مونن.
     */
    public function generateForBooth(Booth $booth, Carbon $periodStart, Carbon $periodEnd): ?Settlement
    {
        return DB::transaction(function () use ($booth, $periodStart, $periodEnd) {

            $rows = WalletTransaction::where('booth_id', $booth->id)
                ->whereNull('settlement_id')
                ->whereIn('type', [
                    WalletTransaction::TYPE_SALE,
                    WalletTransaction::TYPE_REVERSAL,
                    WalletTransaction::TYPE_ADJUSTMENT,
                ])
                ->whereDate('created_at', '<=', $periodEnd)
                ->lockForUpdate()
                ->get();

            if ($rows->isEmpty()) {
                return null;
            }

            $payable = (int) $rows->sum('amount');

            if ($payable <= 0) {
                return null;
            }

            $sales = $rows->whereIn('type', [WalletTransaction::TYPE_SALE, WalletTransaction::TYPE_REVERSAL]);

            $settlement = Settlement::create([
                'booth_id' => $booth->id,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'total_sales' => (int) $sales->sum('gross_amount'),
                'commission_amount' => (int) $sales->sum('commission_amount'),
                'payable_amount' => $payable,
                'status' => 'pending',
            ]);

            WalletTransaction::whereIn('id', $rows->pluck('id'))->update(['settlement_id' => $settlement->id]);

            // سازگاری با گزارش‌های قبلی که هنوز از order_items.settlement_id می‌خونن
            $itemIds = $rows->where('type', WalletTransaction::TYPE_SALE)->pluck('order_item_id')->filter();
            if ($itemIds->isNotEmpty()) {
                OrderItem::whereIn('id', $itemIds)->update(['settlement_id' => $settlement->id]);
            }

            return $settlement;
        });
    }

    /** اجرای این متد برای همه غرفه‌های دارای مانده تسویه‌نشده */
    public function generateForAllBooths(Carbon $periodStart, Carbon $periodEnd): int
    {
        $count = 0;

        Booth::approved()->each(function (Booth $booth) use ($periodStart, $periodEnd, &$count) {
            if ($this->generateForBooth($booth, $periodStart, $periodEnd)) {
                $count++;
            }
        });

        return $count;
    }
}
