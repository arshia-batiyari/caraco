<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * درخواست‌های ارتباط با مالک ملک/زمین. عمداً به‌جای رابطه با booth یا
     * order، یک جدول مستقل و ساده است چون فقط برای ادمین قابل مشاهده است —
     * مالک ملک هرگز به این جدول دسترسی داده نمی‌شود (نه در کنترلر، نه در ویو).
     */
    public function up(): void
    {
        Schema::create('property_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // اگر کاربر لاگین کرده باشد

            $table->string('name');
            $table->string('phone');
            $table->enum('inquiry_type', ['buy', 'partnership', 'other'])->default('other');
            $table->text('message')->nullable();

            // فقط ادمین این وضعیت را می‌بیند و تغییر می‌دهد
            $table->enum('status', ['new', 'contacted', 'archived'])->default('new')->index();
            $table->text('admin_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_inquiries');
    }
};
