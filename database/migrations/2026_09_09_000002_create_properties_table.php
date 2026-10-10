<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // زمین‌دار/ملک‌دار مالک آگهی

            $table->json('title');
            $table->json('description')->nullable();

            // نوع دارایی
            $table->enum('property_type', ['land', 'apartment', 'villa', 'commercial', 'other'])->default('land')->index();

            // نوع معامله مدنظر مالک: فروش، مشارکت در ساخت، یا هردو
            $table->enum('deal_type', ['sale', 'partnership', 'both'])->default('sale')->index();

            $table->unsignedInteger('area_sqm')->nullable(); // متراژ
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('document_type')->nullable(); // نوع سند (تک‌برگ، قولنامه‌ای و ...)

            $table->unsignedBigInteger('price')->nullable(); // قیمت به تومان — اختیاری (مشارکت لزوماً قیمت ثابت ندارد)

            $table->json('images')->nullable(); // مسیر عکس‌ها (آرایه)

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
