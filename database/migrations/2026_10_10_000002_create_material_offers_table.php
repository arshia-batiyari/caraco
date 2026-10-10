<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * پیشنهاد قیمت تأمین‌کننده برای یک نیاز مصالح.
     * این رکورد فقط به ادمین نشان داده می‌شود تا دو طرف را به هم وصل کند.
     */
    public function up(): void
    {
        Schema::create('material_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booth_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('phone');
            $table->unsignedBigInteger('quoted_amount')->nullable();
            $table->unsignedSmallInteger('lead_days')->nullable();
            $table->text('message')->nullable();

            $table->string('status', 32)->default('new')->index();
            $table->text('admin_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_offers');
    }
};
