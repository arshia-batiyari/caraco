<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // مثلاً MYD-140409-0001
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedBigInteger('items_amount');   // جمع محصولات
            $table->unsignedBigInteger('shipping_amount')->default(0);
            $table->unsignedBigInteger('total_amount');   // مبلغ نهایی پرداختی

            // وضعیت کلی سفارش (مستقل از پرداخت)
            $table->enum('status', [
                'pending_payment', 'processing', 'shipped', 'delivered', 'cancelled',
            ])->default('pending_payment')->index();

            // وضعیت پرداخت
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->index();
            $table->string('payment_gateway')->default('zarinpal');
            $table->string('payment_authority')->nullable()->index(); // Authority زرین‌پال
            $table->string('payment_ref_id')->nullable();             // RefID بعد از تأیید تراکنش
            $table->timestamp('paid_at')->nullable();
			$table->string('payment_receipt_path')->nullable()->after('payment_ref_id');
            $table->timestamp('payment_submitted_at')->nullable()->after('payment_receipt_path');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
