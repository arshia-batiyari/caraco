<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * سرویس ارسال پیامک از طریق کاوه‌نگار (kavenegar.com).
 *
 * برای فعال‌سازی، فقط این مقدار رو در .env پر کنید:
 *   KAVENEGAR_API_KEY=your-api-key-here
 *
 * و الگوهای پیامکی (Pattern) که در config/kavenegar.php لیست شده رو
 * از پنل کاوه‌نگار بسازید (بخش «ارسال با الگو / Verify Lookup»).
 * بدون این الگوها، پیامک‌های تراکنشی (OTP، تغییر وضعیت سفارش و...) ارسال نمی‌شن.
 *
 * از REST API مستقیم کاوه‌نگار استفاده شده (بدون نیاز به نصب SDK جداگانه)
 * چون خیلی سبک‌تره و کنترل بیشتری روی خطاها می‌ده.
 */
class SmsService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('kavenegar.api_key');
        $this->baseUrl = "https://api.kavenegar.com/v1/{$this->apiKey}";
    }

    /**
     * ارسال کد تأیید یک‌بارمصرف (برای احراز هویت شماره موبایل).
     */
    public function sendOtp(string $phone, string $code): bool
    {
        return $this->sendByTemplate(config('kavenegar.templates.otp'), $this->normalize($phone), [$code]);
    }

    public function notifyOrderPaid(string $phone, string $orderNumber): bool
    {
        return $this->sendByTemplate(config('kavenegar.templates.order_paid'), $this->normalize($phone), [$orderNumber]);
    }

    public function notifyOrderShipped(string $phone, string $orderNumber, string $trackingCode): bool
    {
        return $this->sendByTemplate(config('kavenegar.templates.order_shipped'), $this->normalize($phone), [$orderNumber, $trackingCode]);
    }

    public function notifyOrderDelivered(string $phone, string $orderNumber): bool
    {
        return $this->sendByTemplate(config('kavenegar.templates.order_delivered'), $this->normalize($phone), [$orderNumber]);
    }

    public function notifyNewOrderToVendor(string $phone, string $boothName, string $orderNumber, string $amountFormatted): bool
    {
        return $this->sendByTemplate(config('kavenegar.templates.new_order_vendor'), $this->normalize($phone), [$boothName, $orderNumber, $amountFormatted]);
    }

    public function notifyBoothApproved(string $phone, string $boothName): bool
    {
        return $this->sendByTemplate(config('kavenegar.templates.booth_approved'), $this->normalize($phone), [$boothName]);
    }

    public function notifyBoothRejected(string $phone, string $boothName, string $reason): bool
    {
        return $this->sendByTemplate(config('kavenegar.templates.booth_rejected'), $this->normalize($phone), [$boothName, $reason]);
    }

    public function notifySettlementPaid(string $phone, string $amountFormatted, string $paymentReference): bool
    {
        return $this->sendByTemplate(config('kavenegar.templates.settlement_paid'), $this->normalize($phone), [$amountFormatted, $paymentReference]);
    }

    /**
     * ارسال یک پیامک آزاد (بدون الگو) — فقط برای مواردی که الگوی از پیش تعریف‌شده ندارند.
     * توجه: پیامک بدون الگو ممکنه به‌عنوان تبلیغاتی شناخته بشه و نیاز به خط مجزا داشته باشه.
     */
    public function sendRaw(string $phone, string $message): bool
    {
        if (! $this->apiKey) {
            Log::warning('Kavenegar: API key not set, SMS skipped.', ['phone' => $phone, 'message' => $message]);
            return false;
        }

        try {
            $response = Http::asForm()->get("{$this->baseUrl}/sms/send.json", array_filter([
                'receptor' => $this->normalize($phone),
                'sender'   => config('kavenegar.sender') ?: null,
                'message'  => $message,
            ]));

            return $this->handleResponse($response, $phone);
        } catch (\Throwable $e) {
            Log::error('Kavenegar sendRaw failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * هسته اصلی: ارسال پیامک تراکنشی با الگوی از پیش ثبت‌شده در پنل کاوه‌نگار (VerifyLookup).
     *
     * @param  array<int, string>  $tokens  حداکثر ۳ مقدار، به ترتیب جای {token}, {token2}, {token3} می‌شینن.
     */
    protected function sendByTemplate(string $templateName, string $phone, array $tokens): bool
    {
        if (! $this->apiKey) {
            Log::warning('Kavenegar: API key not set, SMS skipped.', [
                'template' => $templateName, 'phone' => $phone, 'tokens' => $tokens,
            ]);
            return false;
        }

        try {
            $params = [
                'receptor' => $phone,
                'template' => $templateName,
                'type'     => 'sms',
                'token'    => $tokens[0] ?? '',
            ];
            if (isset($tokens[1])) $params['token2'] = $tokens[1];
            if (isset($tokens[2])) $params['token3'] = $tokens[2];

            $response = Http::asForm()->get("{$this->baseUrl}/verify/lookup.json", $params);

            return $this->handleResponse($response, $phone, $templateName);
        } catch (\Throwable $e) {
            Log::error('Kavenegar sendByTemplate failed', [
                'template' => $templateName, 'phone' => $phone, 'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected function handleResponse($response, string $phone, ?string $template = null): bool
    {
        $body = $response->json();
        $status = $body['return']['status'] ?? null;

        // 200 یعنی موفق؛ کدهای دیگه (مثل 411 شماره نامعتبر، 418 اعتبار ناکافی) یعنی خطا
        if ($status === 200) {
            return true;
        }

        Log::error('Kavenegar API error', [
            'phone' => $phone,
            'template' => $template,
            'status' => $status,
            'message' => $body['return']['message'] ?? 'unknown',
        ]);

        return false;
    }

    /**
     * شماره‌ها رو به فرمتی که کاوه‌نگار می‌خواد (۰۹xxxxxxxxx) نرمال می‌کنه.
     * برای پوشش سراسری کشور، اعداد فارسی/عربی هم به انگلیسی تبدیل می‌شن
     * (چون کاربران از شهرهای مختلف ممکنه با کیبورد فارسی تایپ کرده باشن).
     *
     * public + static چون هم داخل این سرویس و هم موقع ورود با کد پیامکی
     * (قبل از اینکه کاربر لاگین باشه) به همین نرمال‌سازی نیاز داریم.
     */
    public static function normalizePhone(string $phone): string
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

    protected function normalize(string $phone): string
    {
        return self::normalizePhone($phone);
    }
}
