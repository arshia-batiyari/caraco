<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * وقتی یه مالک ملک، آگهیِ «درخواست» یکی رو می‌بینه و فکر می‌کنه ملکش
     * جوابگوعه، دکمه‌ی «ارتباط بگیر» می‌زنه — این فقط یه پیام برای ادمینه
     * (مثل property_inquiries)، نه ارتباط مستقیم با درخواست‌دهنده.
     */
    public function up(): void
    {
        Schema::create('property_request_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('phone');
            $table->text('message')->nullable();

            $table->enum('status', ['new', 'contacted', 'archived'])->default('new')->index();
            $table->text('admin_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_request_offers');
    }
};
