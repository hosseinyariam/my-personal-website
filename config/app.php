<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'Asia/Tehran'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'fa'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'fa'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'fa_IR'),

    /*
    |--------------------------------------------------------------------------
    | فهرست مجاز مدیران پنل
    |--------------------------------------------------------------------------
    |
    | فهرست ایمیل‌هایی که مجاز به ورود به /admin هستند، با کاما جدا شده.
    | خالی بودنش یعنی هیچ‌کس مجاز نیست — شکست امن، نه باز. اگر این مقدار
    | ناخواسته پاک شود، پنل قفل می‌ماند و سایت عمومی سالم کار می‌کند.
    |
    */

    'admin_emails' => env('ADMIN_EMAIL', ''),

    /*
    |--------------------------------------------------------------------------
    | اطلاعات ساخت اولین کاربر مدیر
    |--------------------------------------------------------------------------
    |
    | ── چرا از `env()` مستقیم در seeder استفاده نمی‌کنیم ──
    | وقتی `config:cache` اجرا شده باشد (که در تولید همیشه هست)،
    | فراخوانی `env()` بیرون از فایل‌های `config/` مقدار **null**
    | برمی‌گرداند. یعنی seeder که `env('ADMIN_PASSWORD')` را می‌خواند
    | در محیط تولید همیشه خالی می‌گرفت و کاربر ساخته نمی‌شد —
    | بی‌آنکه پیام روشنی بدهد.
    |
    | راه‌حل: مقدار از config خوانده می‌شود، چون config در زمان
    | اجرای `config:cache` یک‌بار از env پر و ذخیره می‌شود. این
    | الگوی رسمی لاراول است.
    |
    | توجه: این مقدار فقط برای ساخت **اولین** کاربر استفاده می‌شود.
    | تغییر رمز از پنل، در دیتابیس ذخیره می‌شود و این مقدار
    | دست‌نخورده می‌ماند — که مشکلی ندارد، چون seeder کاربر
    | موجود را بازنویسی نمی‌کند.
    |
    */

    'admin_name' => env('ADMIN_NAME', ''),

    'admin_password' => env('ADMIN_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
