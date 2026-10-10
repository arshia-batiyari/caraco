<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * «آگهیِ درخواست ملک» — برعکسِ properties: اینجا کسی که دنبال ملک/زمین
     * می‌گرده (برای خرید، مشارکت یا تهاتر) خواسته‌ش رو عمومی ثبت می‌کنه تا
     * مالکین ببینن و به سایت مراجعه کنن، نه برعکس. مثل properties، هر آگهی
     * قبل از نمایش عمومی باید توسط ادمین تأیید بشه، و اطلاعات تماس
     * درخواست‌دهنده فقط پیش ادمینه (همون منطق حریم خصوصی property_inquiries).
     */
    public function up(): void
    {
        Schema::create('property_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // اگر لاگین کرده باشه

            $table->string('name'); // نام درخواست‌دهنده — فقط برای ادمین
            $table->string('phone'); // فقط برای ادمین

            $table->json('title');
            $table->json('description')->nullable();

            $table->enum('property_type', ['land', 'apartment', 'villa', 'commercial', 'other'])->default('land')->index();
            $table->enum('deal_type', ['sale', 'partnership', 'barter', 'both'])->default('sale')->index();

            $table->unsignedInteger('min_area_sqm')->nullable();
            $table->unsignedInteger('max_area_sqm')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->unsignedBigInteger('budget')->nullable(); // بودجه تقریبی — اختیاری

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_requests');
    }
};
