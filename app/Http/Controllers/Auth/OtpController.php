<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    public function __construct(protected SmsService $sms) {}

    /** صفحه ورود کد تأیید (بعد از ثبت‌نام نمایش داده می‌شود) */
    public function create()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->phone_verified_at) {
            return redirect()->route('home');
        }

        return view('auth.verify-phone', ['phone' => $user->phone]);
    }

    /** ارسال (یا ارسال مجدد) کد تأیید به شماره کاربر لاگین‌شده */
    public function send(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user, 403);

        // جلوگیری از ارسال مکرر: حداقل ۶۰ ثانیه فاصله بین دو درخواست
        $lastOtp = OtpCode::where('phone', $user->phone)->latest()->first();
        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < 60) {
            return back()->with('error', 'لطفاً کمی صبر کنید و دوباره تلاش کنید.');
        }

        $otp = OtpCode::generateFor($user->phone);
        $this->sms->sendOtp($user->phone, $otp->code);

        return back()->with('success', 'کد تأیید برای شماره '.$user->phone.' پیامک شد.');
    }

    /** بررسی کد وارد‌شده توسط کاربر */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|digits:5']);

        $user = Auth::user();
        abort_unless($user, 403);

        $otp = OtpCode::where('phone', $user->phone)->whereNull('used_at')->latest()->first();

        if (! $otp || ! $otp->isValid()) {
            return back()->with('error', 'کد وارد‌شده منقضی شده یا نامعتبر است. کد جدید درخواست کنید.');
        }

        if ($otp->code !== $request->code) {
            $otp->increment('attempts');
            return back()->with('error', 'کد وارد‌شده اشتباه است.');
        }

        $otp->update(['used_at' => now()]);
        $user->update(['phone_verified_at' => now()]);

        // بعد از تأیید شماره، هرکس بر اساس نقشش به مسیر درست هدایت می‌شود
        $redirect = match ($user->role) {
            'admin'  => route('admin.dashboard'),
            'vendor' => $user->booth?->status === 'approved' ? route('vendor.dashboard') : route('vendor.pending'),
            'landowner' => route('landowner.dashboard'),
            default  => route('home'),
        };

        return redirect($redirect)->with('success', 'شماره موبایل شما با موفقیت تأیید شد.');
    }
}
