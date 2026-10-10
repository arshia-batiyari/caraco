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
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function __construct(protected SmsService $sms) {}

    public function create(): \Illuminate\View\View
    {
        return view('auth.register');
    }

    /** ثبت‌نام یک کاربر عادی (مشتری) */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['required', 'string', 'max:20', 'unique:users,phone'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'customer',
        ]);

        event(new Registered($user));
        Auth::login($user, remember: true);

        $otp = OtpCode::generateFor($user->phone);
        $this->sms->sendOtp($user->phone, $otp->code);

        return redirect()->route('verify-phone')->with('success', 'کد تأیید برای شماره شما پیامک شد.');
    }
}
