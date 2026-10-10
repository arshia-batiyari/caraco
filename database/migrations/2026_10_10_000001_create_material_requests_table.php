<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * نیاز مصالح یک پروژه (سیمان، میلگرد، شن، تهاتر و …).
     * مثل درخواست ملک، قبل از نمایش عمومی باید ادمین تأیید کند و
     * نام و تلفن درخواست‌دهنده فقط در پنل ادمین دیده می‌شود.
     */
    public function up(): void
    {
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('phone');

            $table->json('title');
            $table->json('description')->nullable();

            $table->string('material_type', 32)->default('other')->index();
            $table->string('fulfillment', 32)->default('purchase')->index();
            $table->string('quantity_label');
            $table->date('needed_on')->nullable();
            $table->string('province');
            $table->string('city')->nullable();
            $table->string('delivery_place')->nullable();
            $table->unsignedBigInteger('budget')->nullable();

            $table->string('status', 32)->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};
