<?php

return [
    // فقط این خط رو تو .env پر کنید — از پنل kavenegar.com > API Key
    'api_key' => env('KAVENEGAR_API_KEY', ''),

    // شماره خط ارسال (اختیاری — اگر خط اختصاصی دارید؛ وگرنه خالی بگذارید تا خط پیش‌فرض حساب استفاده شود)
    'sender' => env('KAVENEGAR_SENDER', ''),

    // -----------------------------------------------------------------
    // نام الگوهای پیامکی (Pattern) که باید از پنل کاوه‌نگار بسازید:
    // kavenegar.com -> پیامک -> ارسال با الگو (Verify Lookup)
    // چون این پیامک‌ها تراکنشی‌اند (نه تبلیغاتی)، ارسال با الگو سریع‌تر
    // می‌رسه و نیازی به ثبت سرشماره تبلیغاتی نداره.
    //
    // اسم هر الگو رو دقیقاً همینی که اینجا نوشته شده تو پنل کاوه‌نگار بسازید،
    // با توکن‌های {token}, {token2}, {token3} به همین ترتیب.
    // -----------------------------------------------------------------
    'templates' => [
        // {token}: کد ۵ رقمی یک‌بارمصرف
        'otp' => env('KAVENEGAR_TEMPLATE_OTP', 'meydan-otp'),

        // {token}: شماره سفارش
        'order_paid' => env('KAVENEGAR_TEMPLATE_ORDER_PAID', 'meydan-order-paid'),

        // {token}: شماره سفارش, {token2}: کد رهگیری پستی
        'order_shipped' => env('KAVENEGAR_TEMPLATE_ORDER_SHIPPED', 'meydan-order-shipped'),

        // {token}: شماره سفارش
        'order_delivered' => env('KAVENEGAR_TEMPLATE_ORDER_DELIVERED', 'meydan-order-delivered'),

        // {token}: نام غرفه, {token2}: شماره سفارش, {token3}: مبلغ
        'new_order_vendor' => env('KAVENEGAR_TEMPLATE_NEW_ORDER_VENDOR', 'meydan-new-order-vendor'),

        // {token}: نام غرفه
        'booth_approved' => env('KAVENEGAR_TEMPLATE_BOOTH_APPROVED', 'meydan-booth-approved'),

        // {token}: نام غرفه, {token2}: دلیل رد
        'booth_rejected' => env('KAVENEGAR_TEMPLATE_BOOTH_REJECTED', 'meydan-booth-rejected'),

        // {token}: مبلغ واریزی, {token2}: شماره پیگیری بانکی
        'settlement_paid' => env('KAVENEGAR_TEMPLATE_SETTLEMENT_PAID', 'meydan-settlement-paid'),
    ],
];
