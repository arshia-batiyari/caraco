<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->string('code', 6);
            $table->unsignedTinyInteger('attempts')->default(0); // برای جلوگیری از حدس‌زنی کد
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone_verified_at');
        });
    }
};
