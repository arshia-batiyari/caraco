<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VendorRegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| مسیرهای احراز هویت
|--------------------------------------------------------------------------
| اگر بعداً بسته Breeze را نصب کردید (php artisan breeze:install blade)،
| می‌توانید این فایل را با نسخه پیش‌فرض Breeze جایگزین یا ادغام کنید.
| فرم‌های ثبت‌نام مشتری/غرفه‌دار در این پروژه سفارشی‌سازی شده‌اند.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    // ورود با کد پیامکی (بدون رمز عبور) — برای کاربرانی که رمزشون یادشون رفته
    // یا اصلاً ترجیح می‌دن با موبایل وارد بشن
    Route::get('/login/otp', [\App\Http\Controllers\Auth\OtpLoginController::class, 'create'])->name('login.otp');
    Route::post('/login/otp/send', [\App\Http\Controllers\Auth\OtpLoginController::class, 'send'])->name('login.otp.send');
    Route::get('/login/otp/verify', [\App\Http\Controllers\Auth\OtpLoginController::class, 'showVerify'])->name('login.otp.verify');
    Route::post('/login/otp/verify', [\App\Http\Controllers\Auth\OtpLoginController::class, 'verify'])->name('login.otp.verify.store');

    // ثبت‌نام مشتری عادی
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    // ثبت‌نام غرفه‌دار (فرم جدا با اطلاعات غرفه + آپلود مدرک)
    Route::get('/register/vendor', [VendorRegistrationController::class, 'create'])->name('register.vendor');
    Route::post('/register/vendor', [VendorRegistrationController::class, 'store'])->name('register.vendor.store');

    // ثبت‌نام زمین‌دار/ملک‌دار — ساده‌تر از غرفه‌دار چون مدرک/اطلاعات بانکی لازم ندارد
    Route::get('/register/landowner', [\App\Http\Controllers\Auth\LandownerRegistrationController::class, 'create'])->name('register.landowner');
    Route::post('/register/landowner', [\App\Http\Controllers\Auth\LandownerRegistrationController::class, 'store'])->name('register.landowner.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // تأیید شماره موبایل با کد پیامکی (کاوه‌نگار)
    Route::get('/verify-phone', [\App\Http\Controllers\Auth\OtpController::class, 'create'])->name('verify-phone');
    Route::post('/verify-phone/send', [\App\Http\Controllers\Auth\OtpController::class, 'send'])->name('verify-phone.send');
    Route::post('/verify-phone', [\App\Http\Controllers\Auth\OtpController::class, 'verify'])->name('verify-phone.verify');
});
