<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | قابلیت هوش مصنوعی
    |--------------------------------------------------------------------------
    |
    | در MVP این مقدار `false` است و هیچ درخواست خارجی زده نمی‌شود.
    | فعال‌سازی نیازمند تعریف کاربرد واقعی است (CMS:255-256) و
    | تصمیم صریح مالک سایت.
    |
    */

    'ai' => [
        'enabled' => env('AI_ENABLED', false),

        'max_tokens' => 700,

        'temperature' => 0.4,

        /*
        | فراهم‌کننده‌های شناخته‌شده — فقط برای پیش‌فرض‌های پنل.
        | دامنه‌ی دلخواه هم مجاز است (سازگار با OpenAI) به شرط
        | اینکه از محافظت SSRF رد شود.
        */
        'providers' => [
            'openai' => [
                'label' => 'OpenAI',
                'base_url' => 'https://api.openai.com/v1',
                'model' => 'gpt-4o-mini',
            ],
            'openai_compatible' => [
                'label' => 'سازگار با OpenAI',
                'base_url' => null,
                'model' => null,
            ],
            'openrouter' => [
                'label' => 'OpenRouter',
                'base_url' => 'https://openrouter.ai/api/v1',
                'model' => null,
            ],
            'groq' => [
                'label' => 'Groq',
                'base_url' => 'https://api.groq.com/openai/v1',
                'model' => null,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | آنالیتیکس — Matomo سلف‌هاست، بدون کوکی
    |--------------------------------------------------------------------------
    |
    | IA و PRD: آنالیتیکس بدون کوکی برای احترام به حریم خصوصی.
    | اسکریپت فقط اگر هم URL و هم Site ID تنظیم شده باشد تزریق می‌شود.
    |
    */

    'matomo' => [
        'enabled' => env('MATOMO_ENABLED', false),
        'url' => env('MATOMO_URL'),
        'site_id' => env('MATOMO_SITE_ID'),
    ],

];
