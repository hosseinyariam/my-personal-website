<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * جلوگیری از ایندکس‌شدن مسیرهای مدیریتی.
 *
 * ── چرا middleware و نه exception handler ──
 * نسخه‌ی اول این پوشش در `bootstrap/app.php` داخل `exceptions->respond`
 * بود. آن callback فقط برای **پاسخ‌های خطا** اجرا می‌شود. نتیجه:
 * صفحه‌ی ورود پنل که با ۲۰۰ پاسخ می‌دهد، هدر `X-Robots-Tag` نمی‌گرفت
 * و می‌شد ایندکس شود. middleware روی همه‌ی پاسخ‌ها اجرا می‌شود.
 *
 * ── چرا این لایه لازم است وقتی robots.txt هم Disallow دارد ──
 * `Disallow` در robots.txt جلوی ایندکس‌شدن را نمی‌گیرد — فقط از
 * خزش جلوگیری می‌کند. اگر صفحه‌ای جای دیگری لینک شده باشد، ممکن است
 * بدون خزش هم ایندکس شود. هدر HTTP قوی‌ترین سیگنال است.
 */
class NoIndexAdmin
{
    /** پیشوند مسیرهایی که هرگز نباید ایندکس شوند. */
    private const PROTECTED_PREFIXES = [
        'admin',
        'livewire',
        'filament',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (self::PROTECTED_PREFIXES as $prefix) {
            if ($request->is($prefix) || $request->is($prefix.'/*')) {
                $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

                break;
            }
        }

        return $response;
    }
}
