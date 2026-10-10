<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * قشر جدید کاربران: «زمین‌دار/ملک‌دار» که می‌توانند ملک ثبت کنند اما
     * برخلاف غرفه‌دار، حسابشان نیاز به تأیید ادمین ندارد — فقط خودِ آگهی‌های
     * ملکشان قبل از نمایش عمومی باید تأیید شود (وضعیت روی هر ملک جداگانه است).
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','vendor','customer','landowner') NOT NULL DEFAULT 'customer'");
            return;
        }

        // sqlite (فقط برای محیط تست محلی؛ محیط اصلی پروژه mysql است): ستون role
        // به‌صورت CHECK constraint در متن جدول ذخیره شده و بدون doctrine/dbal
        // نمی‌شود آن را عادی تغییر داد؛ مستقیماً متن schema را اصلاح می‌کنیم.
        if ($driver === 'sqlite') {
            try {
                DB::statement('PRAGMA writable_schema = 1');
                DB::statement("UPDATE sqlite_master SET sql = REPLACE(sql, \"'customer'))\", \"'customer', 'landowner'))\") WHERE type = 'table' AND name = 'users'");
                DB::statement('PRAGMA writable_schema = 0');
            } catch (\Throwable $e) {
                // اگر متن دقیق مطابقت نداشت، نادیده می‌گیریم — روی mysql (محیط واقعی) این مشکل وجود ندارد
            }
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','vendor','customer') NOT NULL DEFAULT 'customer'");
        }
    }
};
