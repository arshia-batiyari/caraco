<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // چون ممکنه یه سفارش شامل محصولات چند غرفه باشه، هر غرفه جدا ارسال می‌کنه
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booth_id')->constrained()->restrictOnDelete();

            $table->string('carrier')->nullable();       // شرکت پست/پیک
            $table->string('tracking_code')->nullable();
            $table->enum('status', ['preparing', 'shipped', 'in_transit', 'delivered', 'returned'])->default('preparing')->index();

            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'booth_id']);
        });

        // پیوند بین آیتم‌های سفارش و مرسوله مربوطه
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('shipment_id')->nullable()->after('booth_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipment_id');
        });
        Schema::dropIfExists('shipments');
    }
};
