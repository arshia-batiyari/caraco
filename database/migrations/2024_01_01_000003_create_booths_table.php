<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('name');
            $table->string('slug')->unique();
            $table->json('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();

            // آیا این غرفه پرداخت آنلاین (زرین‌پال) رو فعال کرده؟
            $table->boolean('online_payment_enabled')->default(false);

            // درصد کمیسیون هلدینگ از فروش این غرفه (قابل تنظیم جدا برای هر غرفه)
            $table->decimal('commission_percent', 5, 2)->default(10.00);

            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('booth_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booth_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unique(['booth_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booth_category');
        Schema::dropIfExists('booths');
    }
};
