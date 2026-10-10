<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * دفتر حساب (Ledger) کیف پول غرفه‌ها.
 *
 * هر ردیف یک سند حسابداریه: مبلغ مثبت = بستانکار (واریز به کیف پول غرفه‌دار)،
 * مبلغ منفی = بدهکار (کسر یا برداشت). موجودی کیف پول همیشه از جمع همین
 * ردیف‌ها به‌دست میاد (نه یه ستون جداگانه)، پس هیچ‌وقت با سوابق ناهماهنگ نمی‌شه.
 *
 * type:
 *   sale        سهم غرفه‌دار از یک آیتم سفارش (بعد از تأیید پرداخت)
 *   reversal    برگشت سهم یک آیتم (رد/لغو پرداخت بعد از تأیید)
 *   adjustment  سند اصلاحی دستی ادمین (بستانکار یا بدهکار، با شرح اجباری)
 *   payout      برداشت/تسویه‌حساب (واریز واقعی به شبای غرفه‌دار)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booth_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->bigInteger('amount');
            $table->bigInteger('gross_amount')->nullable();      // مبلغ کالا (قبل از کمیسیون)
            $table->bigInteger('commission_amount')->nullable(); // کمیسیون هاچ لند از همین آیتم
            $table->decimal('share_percent', 5, 2)->nullable();  // درصد سهم غرفه‌دار در لحظه‌ی ثبت
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('settlement_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description', 500)->nullable();
            $table->string('reference', 100)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['booth_id', 'created_at']);
            $table->unique(['order_item_id', 'type']); // هر آیتم فقط یک‌بار واریز و یک‌بار برگشت می‌خوره
        });

        $this->backfillExistingData();
    }

    /**
     * سفارش‌های پرداخت‌شده‌ی قبل از راه‌اندازی کیف پول رو هم وارد دفتر حساب
     * می‌کنیم تا غرفه‌های فعلی موجودی درست داشته باشن (و تسویه‌های قبلی هم
     * به‌صورت برداشت ثبت می‌شن، پس موجودی دوبار حساب نمی‌شه).
     */
    protected function backfillExistingData(): void
    {
        if (! Schema::hasTable('order_items') || ! Schema::hasTable('orders')) {
            return;
        }

        DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->orderBy('order_items.id')
            ->select([
                'order_items.id as item_id', 'order_items.order_id', 'order_items.booth_id',
                'order_items.line_total', 'order_items.commission_percent', 'order_items.commission_amount',
                'order_items.booth_payout_amount', 'order_items.settlement_id', 'orders.paid_at',
            ])
            ->chunk(500, function ($rows) {
                $insert = [];
                foreach ($rows as $row) {
                    $paidAt = $row->paid_at ?: now();
                    $insert[] = [
                        'booth_id' => $row->booth_id,
                        'type' => 'sale',
                        'amount' => (int) $row->booth_payout_amount,
                        'gross_amount' => (int) $row->line_total,
                        'commission_amount' => (int) $row->commission_amount,
                        'share_percent' => round(100 - (float) $row->commission_percent, 2),
                        'order_id' => $row->order_id,
                        'order_item_id' => $row->item_id,
                        'settlement_id' => $row->settlement_id,
                        'created_at' => $paidAt,
                        'updated_at' => $paidAt,
                    ];
                }
                DB::table('wallet_transactions')->insert($insert);
            });

        DB::table('settlements')->where('status', 'paid')->orderBy('id')->get()->each(function ($s) {
            if ((int) $s->payable_amount <= 0) {
                return;
            }
            DB::table('wallet_transactions')->insert([
                'booth_id' => $s->booth_id,
                'type' => 'payout',
                'amount' => -1 * (int) $s->payable_amount,
                'settlement_id' => $s->id,
                'reference' => $s->payment_reference,
                'created_by' => $s->paid_by,
                'created_at' => $s->paid_at ?: now(),
                'updated_at' => $s->paid_at ?: now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
