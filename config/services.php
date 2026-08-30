<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    // LINE Login channel (คนละตัวกับ Messaging API channel)
    // สร้างที่ https://developers.line.biz/console/
    'line' => [
        'client_id' => env('LINE_CLIENT_ID'),
        'client_secret' => env('LINE_CLIENT_SECRET'),
        'redirect' => env('LINE_REDIRECT_URI', env('APP_URL') . '/auth/line/callback'),
    ],

    // ผู้ให้บริการ SMS สำหรับ OTP — log = เขียนลง log ไม่ส่งจริง ใช้ตอน dev
    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
    ],

    /**
     * SMSMKT — https://developers.smsmkt.com/en/api-reference
     * ฝั่งเขาเป็นคนสร้างและตรวจ OTP เอง เราแค่เก็บ token ไว้อ้างอิง
     */
    'smsmkt' => [
        'api_key' => env('SMSMKT_API_KEY'),
        'secret_key' => env('SMSMKT_SECRET_KEY'),
        'project_key' => env('SMSMKT_PROJECT_KEY'),
        'project_id' => env('SMSMKT_PROJECT_ID'),
        'sender' => env('SMSMKT_SENDER', 'KGM'),
        'enabled' => env('SMSMKT_ENABLED', false),
        'thai_message' => env('SMSMKT_THAI_MESSAGE', true),
    ],

];
