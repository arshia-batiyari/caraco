<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

/**
 * ثبت‌نام «زمین‌دار/ملک‌دار». برخلاف غرفه‌دار، اینجا نیازی به مدرک هویتی یا
 * اطلاعات بانکی نیست — چون این کاربران چیزی نمی‌فروشند، فقط آگهی ملک ثبت
 * می‌کنند و هر آگهی جداگانه قبل از نمایش عمومی توسط ادمین بررسی می‌شود.
 * پس خودِ حساب کاربری بلافاصله بعد از تأیید شماره موبایل فعال است.
 */
class LandownerRegistrationController extends Controller
{
    public function __construct(protected SmsService $sms) {}

    public function create(): \Illuminate\View\View
    {
        return view('auth.register-landowner');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['required', 'string', 'max:20', 'unique:users,phone'],
            'email'    => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'phone'    => $data['phone'],
                'email'    => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role'     => 'landowner',
            ]);

            event(new Registered($user));
            Auth::login($user, remember: true);

            $otp = OtpCode::generateFor($user->phone);
            $this->sms->sendOtp($user->phone, $otp->code);

            return $user;
        });

        return redirect()->route('verify-phone')
            ->with('success', 'کد تأیید برای شماره شما پیامک شد. بعد از تأیید شماره می‌توانید ملک یا زمین خود را ثبت کنید.');
    }
}
