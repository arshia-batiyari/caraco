<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->json('title'); // عنوان اگهی — {fa: ..., en: ...}
            $table->json('message')->nullable(); // توضیح تکمیلی — اختیاری

            // نوع اگهی — روی رنگ و آیکون نواری که تو سایت نمایش داده می‌شه اثر می‌ذاره
            $table->enum('type', ['info', 'offer', 'urgent'])->default('info')->index();

            $table->string('link_url')->nullable(); // لینک دکمه (اختیاری)
            $table->string('link_text')->nullable(); // متن دکمه (اختیاری)

            // هرچی عدد بزرگ‌تر، اولویت نمایش بالاتر (وقتی چند اگهی هم‌زمان فعالن)
            $table->unsignedTinyInteger('priority')->default(0);

            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable(); // اگه خالی باشه، از همین الان فعاله
            $table->timestamp('ends_at')->nullable(); // اگه خالی باشه، تا وقتی دستی غیرفعال نشه می‌مونه

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};