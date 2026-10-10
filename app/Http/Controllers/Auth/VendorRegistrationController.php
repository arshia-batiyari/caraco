<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Booth;
use App\Models\Category;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class VendorRegistrationController extends Controller
{
    public function __construct(protected SmsService $sms) {}

    public function create(): \Illuminate\View\View
    {
        $categories = Category::roots()->where('is_active', true)->orderBy('sort_order')->get();

        return view('auth.register-vendor', compact('categories'));
    }

    /**
     * ثبت‌نام غرفه‌دار: هم‌زمان یک User با نقش vendor و یک Booth با وضعیت
     * "در انتظار تأیید" ساخته می‌شود. غرفه تا تأیید ادمین در سایت نمایش داده نمی‌شود.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // اطلاعات حساب کاربری
            'owner_name' => ['required', 'string', 'max:255'],
            'phone'      => ['required', 'string', 'max:20', 'unique:users,phone'],
            'email'      => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'confirmed', Rules\Password::defaults()],

            // اطلاعات غرفه
            'booth_name' => ['required', 'string', 'max:255'],
            'province'   => ['required', 'string', 'max:100'],
            'city'       => ['required', 'string', 'max:100'],
            'booth_phone'=> ['required', 'string', 'max:20'],
            'national_id'=> ['required', 'string', 'max:20'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['exists:categories,id'],
            'description'=> ['nullable', 'string', 'max:1000'],

            // اطلاعات بانکی — بدون این، ادمین نمی‌تواند تسویه‌حساب واریز کند
            'bank_account_holder' => ['required', 'string', 'max:255'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_iban' => ['required', 'string', 'regex:/^IR[0-9]{24}$/i'],

            // مدرک هویتی/جواز کسب — الزامی برای احراز هویت قبل از تأیید
            'verification_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ], [
            'verification_document.required' => 'برای بررسی و تأیید غرفه، آپلود مدرک هویتی یا جواز کسب الزامی است.',
            'bank_iban.regex' => 'شماره شبا باید با IR شروع شود و شامل ۲۴ رقم بعد از آن باشد (مثال: IR820540102680020817909002).',
        ]);

        DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name'     => $data['owner_name'],
                'phone'    => $data['phone'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => 'vendor',
            ]);

            $documentPath = $request->file('verification_document')->store('vendor-documents', 'local');
            $logoPath = $request->hasFile('logo') ? $request->file('logo')->store('booths/logos', 'public') : null;

            $booth = Booth::create([
                'user_id'     => $user->id,
                'name'        => ['fa' => $data['booth_name'], 'en' => $data['booth_name']],
                'slug'        => Str::slug($data['booth_name']) . '-' . Str::random(5),
                'description' => ['fa' => $data['description'] ?? ''],
                'logo'        => $logoPath,
                'verification_document' => $documentPath,
                'national_id' => $data['national_id'],
                'province'    => $data['province'],
                'city'        => $data['city'],
                'phone'       => $data['booth_phone'],
                'status'      => 'pending', // تا بررسی ادمین، غیرفعال است
                'online_payment_enabled' => false, // بعد از تأیید، خودش می‌تواند فعال کند
                'commission_percent' => 10.00, // درصد پیش‌فرض؛ ادمین می‌تواند بعداً تغییر دهد
                'bank_account_holder' => $data['bank_account_holder'],
                'bank_name' => $data['bank_name'],
                'bank_iban' => strtoupper($data['bank_iban']),
            ]);

            $booth->categories()->sync($data['categories']);

            event(new Registered($user));
            Auth::login($user, remember: true);

            $otp = OtpCode::generateFor($user->phone);
            $this->sms->sendOtp($user->phone, $otp->code);
        });

        return redirect()->route('verify-phone')
            ->with('success', 'کد تأیید برای شماره شما پیامک شد. بعد از تأیید، درخواست غرفه شما بررسی خواهد شد.');
    }
}
