<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * سرویس اتصال به درگاه پرداخت زرین‌پال (API نسخه ۴).
 *
 * برای فعال‌سازی، فقط کافیه این مقادیر رو در فایل .env پروژه پر کنید:
 *   ZARINPAL_MERCHANT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
 *   ZARINPAL_SANDBOX=false   (برای محیط واقعی/پروداکشن)
 *   ZARINPAL_CALLBACK_URL=https://yourdomain.com/checkout/callback
 *
 * هیچ خط دیگه‌ای از این فایل نیازی به تغییر نداره.
 */
class ZarinPalService
{
    protected string $merchantId;
    protected array $urls;

    public function __construct()
    {
        $this->merchantId = config('zarinpal.merchant_id');
        $this->urls = config('zarinpal.urls');
    }

    /**
     * درخواست پرداخت برای یک سفارش و برگرداندن آدرس ریدایرکت به درگاه.
     *
     * نکته: زرین‌پال مبلغ رو بر حسب "ریال" می‌خواد، پس مبلغ تومانی سفارش را ×۱۰ می‌کنیم.
     */
    public function requestPayment(Order $order): string
    {
        $amountRial = $order->total_amount * 10;

        $response = Http::asJson()->post($this->urls['request'], [
            'merchant_id'  => $this->merchantId,
            'amount'       => $amountRial,
            'callback_url' => route('checkout.callback'),
            'description'  => "پرداخت سفارش شماره {$order->order_number} — هاچ لند",
            'metadata'     => [
                'mobile' => $order->user->phone,
                'email'  => $order->user->email,
                'order_id' => $order->id,
            ],
        ]);

        $result = $response->json();

        if (($result['data']['code'] ?? null) !== 100) {
            Log::error('ZarinPal payment request failed', [
                'order_id' => $order->id,
                'response' => $result,
            ]);

            throw new \RuntimeException(
                'خطا در اتصال به درگاه پرداخت: ' . ($result['errors']['message'] ?? 'خطای نامشخص')
            );
        }

        $authority = $result['data']['authority'];

        $order->update(['payment_authority' => $authority]);

        return $this->urls['start_pay'] . $authority;
    }

    /**
     * تأیید تراکنش بعد از بازگشت کاربر از درگاه (در callback).
     *
     * @return array{success: bool, ref_id: ?string, message: string}
     */
    public function verifyPayment(Order $order): array
    {
        $amountRial = $order->total_amount * 10;

        $response = Http::asJson()->post($this->urls['verify'], [
            'merchant_id' => $this->merchantId,
            'amount'      => $amountRial,
            'authority'   => $order->payment_authority,
        ]);

        $result = $response->json();
        $code = $result['data']['code'] ?? null;

        // 100 = تراکنش موفق و برای اولین بار تأیید شد
        // 101 = تراکنش قبلاً هم تأیید شده بود (idempotent — نباید دوباره verify رو خطا حساب کرد)
        if ($code === 100 || $code === 101) {
            return [
                'success' => true,
                'ref_id'  => (string) ($result['data']['ref_id'] ?? ''),
                'message' => 'پرداخت با موفقیت تأیید شد.',
            ];
        }

        Log::warning('ZarinPal payment verification failed', [
            'order_id' => $order->id,
            'response' => $result,
        ]);

        return [
            'success' => false,
            'ref_id'  => null,
            'message' => $result['errors']['message'] ?? 'پرداخت ناموفق بود یا لغو شد.',
        ];
    }
}
