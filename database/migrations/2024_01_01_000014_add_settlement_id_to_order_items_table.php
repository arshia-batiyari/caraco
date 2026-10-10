<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * این ستون قبلاً داخل migration ساخت order_items تعریف شده بود، اما چون
     * جدول settlements دو migration بعدتر ساخته می‌شه، فارین‌کی موقع ساخت
     * جدول order_items خطا می‌داد (جدول مقصد هنوز وجود نداشت). به همین خاطر
     * این ستون به یک migration جدا، بعد از ساخت settlements، منتقل شده.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // آیا این آیتم قبلاً در یک settlement لحاظ شده؟
            $table->foreignId('settlement_id')->nullable()->after('item_status')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('settlement_id');
        });
    }
};
