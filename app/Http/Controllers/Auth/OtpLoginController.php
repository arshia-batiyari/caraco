<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ورود با شماره موبایل + کد پیامکی، بدون نیاز به رمز عبور.
 * برای یک سایت سراسری که خیلی از کاربرانش موبایل رو راحت‌تر از ایمیل/رمز
 * چک می‌کنن، این معمولاً پرکاربردترین روش ورود میشه.
 */
class OtpLoginController extends Controller
{
    public function __construct(protected SmsService $sms) {}

    /** فرم وارد کردن شماره موبایل */
    public function create()
    {
        return view('auth.login-otp');
    }

    /** ارسال کد به شماره‌ای که قبلاً تو سایت ثبت‌نام کرده */
    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate(['phone' => 'required|string|max:20']);
        $phone = $this->normalize($data['phone']);

        $user = User::where('phone', $phone)->first();

        if (! $user) {
            return back()->withInput()->with('error', 'حسابی با این شماره موبایل پیدا نشد. ابتدا ثبت‌نام کنید.');
        }

        // جلوگیری از ارسال مکرر: حداقل ۶۰ ثانیه فاصله بین دو درخواست
        $lastOtp = OtpCode::where('phone', $phone)->latest()->first();
        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < 60) {
            return back()->withInput()->with('error', 'لطفاً کمی صبر کنید و دوباره تلاش کنید.');
        }

        $otp = OtpCode::generateFor($phone);

        if (! $this->sms->sendOtp($phone, $otp->code)) {
            $otp->delete();
            return back()->withInput()->with('error', 'ارسال پیامک ناموفق بود. لطفاً دقایقی بعد دوباره تلاش کنید.');
        }

        session(['otp_login_phone' => $phone]);

        return redirect()->route('login.otp.verify')->with('success', 'کد ورود برای شماره '.$phone.' پیامک شد.');
    }

    /** فرم وارد کردن کد */
    public function showVerify()
    {
        $phone = session('otp_login_phone');

        if (! $phone) {
            return redirect()->route('login.otp');
        }

        return view('auth.login-otp-verify', compact('phone'));
    }

    /** بررسی کد و ورود کاربر بدون رمز عبور */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|digits:5']);

        $phone = session('otp_login_phone');
        if (! $phone) {
            return redirect()->route('login.otp');
        }

        $otp = OtpCode::where('phone', $phone)->whereNull('used_at')->latest()->first();

        if (! $otp || ! $otp->isValid()) {
            return back()->with('error', 'کد وارد‌شده منقضی شده یا نامعتبر است. کد جدید درخواست کنید.');
        }

        if ($otp->code !== $request->code) {
            $otp->increment('attempts');
            return back()->with('error', 'کد وارد‌شده اشتباه است.');
        }

        $user = User::where('phone', $phone)->firstOrFail();

        $otp->update(['used_at' => now()]);
        // چون کد پیامکی به همون شماره تأیید شد، شماره هم به‌طور خودکار تأییدشده ثبت می‌شود
        if (! $user->phone_verified_at) {
            $user->update(['phone_verified_at' => now()]);
        }

        Auth::login($user, remember: true);
        session()->forget('otp_login_phone');
        $request->session()->regenerate();

        $redirect = match ($user->role) {
            'admin'  => route('admin.dashboard'),
            'vendor' => $user->booth?->status === 'approved' ? route('vendor.dashboard') : route('vendor.pending'),
            'landowner' => route('landowner.dashboard'),
            default  => route('home'),
        };

        return redirect()->intended($redirect)->with('success', 'با موفقیت وارد شدید.');
    }

    protected function normalize(string $phone): string
    {
        $phone = strtr($phone, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
        ]);
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '98')) {
            $phone = '0'.substr($phone, 2);
        } elseif (str_starts_with($phone, '+98')) {
            $phone = '0'.substr($phone, 3);
        }

        return $phone;
    }
}
