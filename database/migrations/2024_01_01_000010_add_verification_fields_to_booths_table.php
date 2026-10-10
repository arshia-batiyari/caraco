<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booths', function (Blueprint $table) {
            // مدرک هویتی/جواز کسب که غرفه‌دار موقع ثبت‌نام آپلود می‌کند — برای بررسی ادمین قبل از تأیید
            $table->string('verification_document')->nullable()->after('banner');
            $table->string('national_id')->nullable()->after('verification_document'); // کد ملی یا شناسه ملی شرکت
        });
    }

    public function down(): void
    {
        Schema::table('booths', function (Blueprint $table) {
            $table->dropColumn(['verification_document', 'national_id']);
        });
    }
};
