<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| محدودیت نرخ — تعریف متمرکز
|--------------------------------------------------------------------------
|
| چرا اینجا و نه در middleware: نرخ فرم تماس باید بر اساس IP باشد،
| با پاسخ فارسی و صفحه‌ی خطای سفارشی. الزام: PRD و CMS هر دو rate
| limit روی فرم‌های حساس را الزام کرده‌اند (CMS:328 و معماری:1042).
|
*/

// ── فرم تماس: ۵ ارسال در ساعت به‌ازای IP ──
// سخت‌گیرانه انتخاب شد چون فرم تماس تنها ورودی عمومی سایت است و
// هدف اصلی اسپم است. کاربر واقعی به‌ندرت بیش از یک بار در ساعت
// پیام می‌فرستد.
RateLimiter::for('contact-submissions', function (Request $request): Limit {
    return Limit::perHour(5)
        ->by($request->ip())
        ->response(fn (): \Illuminate\Contracts\View\View => view('errors.429', [
            'message' => 'تعداد پیام‌های ارسالی شما بیش از حد مجاز است. لطفاً یک ساعت دیگر تلاش کنید.',
        ]));
});

// ── ورود به پنل: ۵ تلاش در دقیقه ──
RateLimiter::for('admin-login', function (Request $request): Limit {
    return Limit::perMinute(5)->by($request->ip());
});

// ── جست‌وجو: ۳۰ درخواست در دقیقه ──
// جست‌وجوی LIKE روی چند جدول می‌تواند روی هاست اشتراکی سنگین باشد.
RateLimiter::for('search', function (Request $request): Limit {
    return Limit::perMinute(30)->by($request->ip());
});

/*
|--------------------------------------------------------------------------
| زمان‌بند — cron هاست
|--------------------------------------------------------------------------
|
| روی هاست اشتراکی یک cron با `schedule:run` هر دقیقه اجرا می‌شود
| (مستندات انتشار، بخش cron).
|
*/

// انتشار محتوایی که زمانش رسیده اما هنوز پیش‌نویس است.
// B:291-292 و E:88-89: تاریخ آینده نباید زودتر منتشر کند.
Schedule::command('contents:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping();

// پاک‌سازی پیام‌های اسپم قدیمی — نگه‌داشتن‌شان بی‌فایده است
// و فضای دیتابیس هاست اشتراکی محدود است.
Schedule::command('contact:prune-spam')
    ->daily()
    ->at('03:30');

// پاک‌سازی کش منقضی — روی driver دیتابیس ضروری است
Schedule::command('cache:prune-stale')
    ->hourly();
