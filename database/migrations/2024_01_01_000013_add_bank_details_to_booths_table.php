<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booths', function (Blueprint $table) {
            // اطلاعات بانکی برای واریز تسویه‌حساب — بدون این، ادمین اصلاً
            // نمی‌داند پول را به کجا واریز کند
            $table->string('bank_account_holder')->nullable()->after('commission_percent');
            $table->string('bank_iban', 26)->nullable()->after('bank_account_holder'); // شماره شبا، بدون IR
            $table->string('bank_name')->nullable()->after('bank_iban');
        });
    }

    public function down(): void
    {
        Schema::table('booths', function (Blueprint $table) {
            $table->dropColumn(['bank_account_holder', 'bank_iban', 'bank_name']);
        });
    }
};
