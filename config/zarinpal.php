<?php

return [

    // فقط همین مقادیر رو باید تو فایل .env پر کنید — به کد دست نزنید
    'merchant_id' => env('ZARINPAL_MERCHANT_ID', ''),

    // در حالت توسعه/تست حتماً true بذارید تا از سرور Sandbox زرین‌پال استفاده بشه
    'sandbox' => env('ZARINPAL_SANDBOX', true),

    // آدرسی که زرین‌پال بعد از پرداخت کاربر رو به اونجا برمی‌گردونه
    'callback_url' => env('ZARINPAL_CALLBACK_URL', '/checkout/callback'),

    // -------------------------------------------------------------
    // از این خط به پایین نیازی به تغییر نیست — آدرس‌های ثابت درگاه
    // -------------------------------------------------------------
    'urls' => [
        'request' => env('ZARINPAL_SANDBOX', true)
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
            : 'https://api.zarinpal.com/pg/v4/payment/request.json',

        'verify' => env('ZARINPAL_SANDBOX', true)
            ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json'
            : 'https://api.zarinpal.com/pg/v4/payment/verify.json',

        'start_pay' => env('ZARINPAL_SANDBOX', true)
            ? 'https://sandbox.zarinpal.com/pg/StartPay/'
            : 'https://www.zarinpal.com/pg/StartPay/',
    ],
];
