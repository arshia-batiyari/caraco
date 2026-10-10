<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('booth_id')->constrained()->restrictOnDelete();

            // snapshot از اطلاعات محصول لحظه خرید (چون قیمت محصول ممکنه بعداً تغییر کنه)
            $table->json('product_name');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total'); // unit_price * quantity

            // کمیسیون این آیتم، محاسبه‌شده لحظه ثبت سفارش
            $table->decimal('commission_percent', 5, 2);
            $table->unsignedBigInteger('commission_amount');
            $table->unsignedBigInteger('booth_payout_amount'); // line_total - commission_amount

            // وضعیت مجزای هر آیتم (چون هر غرفه جدا ارسال می‌کنه)
            $table->enum('item_status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');

            $table->timestamps();

            $table->index(['booth_id', 'item_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
