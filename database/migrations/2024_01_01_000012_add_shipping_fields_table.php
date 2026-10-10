<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // وزن بسته‌بندی‌شده محصول، برای محاسبه هزینه ارسال — پیش‌فرض ۵۰۰ گرم
            $table->unsignedInteger('weight_grams')->default(500)->after('stock');
        });

        Schema::table('orders', function (Blueprint $table) {
            // جزئیات هزینه ارسال به تفکیک هر غرفه: {"3": 40000, "7": 60000}
            // چون سفارش ممکنه شامل چند غرفه از استان‌های مختلف باشه و هرکدوم
            // هزینه ارسال جدا داره؛ shipping_amount مجموع همین مقادیره.
            $table->json('shipping_breakdown')->nullable()->after('shipping_amount');
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_cost')->default(0)->after('booth_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('weight_grams'));
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('shipping_breakdown'));
        Schema::table('shipments', fn (Blueprint $table) => $table->dropColumn('shipping_cost'));
    }
};
