<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoCustomerSeeder extends Seeder
{
    /**
     * یک کاربر مشتری نمونه با یک آدرس ثبت‌شده، برای تست کامل جریان
     * سبد خرید و پرداخت بدون نیاز به ثبت‌نام دستی.
     */
    public function run(): void
    {
        $customer = User::firstOrCreate(
            ['email' => 'customer@meydan.test'],
            [
                'name' => 'مریم صادقی',
                'phone' => '09121234567',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );

        $customer->addresses()->firstOrCreate(
            ['receiver_name' => 'مریم صادقی'],
            [
                'receiver_phone' => '09121234567',
                'province' => 'تهران',
                'city' => 'تهران',
                'address_line' => 'خیابان ولیعصر، بالاتر از میدان ونک، پلاک ۱۲، واحد ۳',
                'postal_code' => '1991634567',
                'is_default' => true,
            ]
        );
    }
}
