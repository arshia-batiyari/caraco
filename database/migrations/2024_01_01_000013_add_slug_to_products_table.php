<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // برای URL تمیز و سئوپسند مثل /product/copper-tray-a8f3k2
            // به‌جای /product/42 — خودکار موقع ساخت محصول پر می‌شود (نگاه کنید به Product::booted())
            $table->string('slug')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
