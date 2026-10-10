<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * ساخت یک حساب ادمین پیش‌فرض برای اولین ورود بعد از نصب.
     * بعد از اولین لاگین، حتماً رمز عبور را از پنل تغییر دهید.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@meydan.test'],
            [
                'name' => 'مدیر سایت',
                'phone' => '09120000000',
                'password' => Hash::make('password'), // حتماً بعد از اولین ورود تغییر دهید
                'role' => 'admin',
            ]
        );
    }
}
