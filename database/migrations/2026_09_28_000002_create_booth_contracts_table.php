<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * قرارداد همکاری هر غرفه با هاچ لند.
 * متن کامل قرارداد (terms) و مشخصات طرفین (parties) در لحظه‌ی صدور «اسنپ‌شات»
 * می‌شه؛ یعنی اگه بعداً متن پیش‌فرض یا مشخصات غرفه عوض بشه، قراردادِ امضاشده
 * دقیقاً همون چیزی می‌مونه که غرفه‌دار دیده و امضا کرده.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booth_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booth_id')->constrained()->cascadeOnDelete();
            $table->string('contract_number', 30)->unique();
            $table->string('status', 20)->default('awaiting_signature'); // awaiting_signature | signed | superseded | void

            $table->decimal('share_percent', 5, 2);
            $table->decimal('commission_percent', 5, 2);
            $table->unsignedSmallInteger('settlement_cycle_days')->default(15);
            $table->unsignedSmallInteger('term_months')->default(12);
            $table->text('special_terms')->nullable();

            $table->json('terms');    // مواد قرارداد (snapshot)
            $table->json('parties');  // مشخصات طرفین (snapshot)
            $table->string('document_hash', 64);

            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();

            // امضای غرفه‌دار
            $table->string('signer_name')->nullable();
            $table->string('signature_path')->nullable();
            $table->boolean('accepted_terms')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->string('signer_ip', 64)->nullable();
            $table->string('signer_user_agent', 500)->nullable();

            $table->timestamp('voided_at')->nullable();
            $table->string('void_reason', 500)->nullable();
            $table->timestamps();

            $table->index(['booth_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booth_contracts');
    }
};
