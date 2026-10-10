<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * چون حالا امکان «ورود/ثبت‌نام سریع با کد پیامکی» اضافه شده (بدون ایمیل/رمز)،
     * این دو فیلد باید nullable بشن. کاربرانی که قبلاً با ایمیل/رمز ثبت‌نام
     * کرده‌اند دست نمی‌خورند.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
