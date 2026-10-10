<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booth_id')->constrained()->restrictOnDelete();

            $table->date('period_start');
            $table->date('period_end');

            $table->unsignedBigInteger('total_sales');       // جمع فروش دوره
            $table->unsignedBigInteger('commission_amount');  // کمیسیون کسرشده
            $table->unsignedBigInteger('payable_amount');     // مبلغ نهایی قابل واریز به غرفه‌دار

            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending')->index();

            $table->string('payment_reference')->nullable(); // شماره پیگیری واریز بانکی
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete(); // کدوم ادمین واریز رو تأیید کرد
            $table->timestamp('paid_at')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
