<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // کد تأیید مالکیت Google Search Console — از Search Console > Settings >
    // Ownership verification > HTML tag کپی کن (فقط مقدار content رو، نه کل تگ)
    // و تو .env بذار: GOOGLE_SITE_VERIFICATION=xxxxxxxx
    'google' => [
        'site_verification' => env('GOOGLE_SITE_VERIFICATION'),
    ],

];