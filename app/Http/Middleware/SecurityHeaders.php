<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * هدرهای امنیتی روی همه‌ی پاسخ‌ها.
 *
 * ── چرا این middleware لازم است ──
 * هیچ‌کدام از ۱۱ سند پروژه هدرهای امنیتی را مشخص نکرده‌اند — این
 * یک شکاف مستنداتی است که دستورالعمل بخش ۱۱ آن را الزام کرده.
 * بدون این هدرها، سایت در برابر clickjacking، MIME sniffing و
 * افشای referrer آسیب‌پذیر است.
 *
 * ── درباره CSP ──
 * CSP سخت‌گیرانه با `unsafe-inline` سازگار نیست، اما Blade و Filament
 * هر دو inline style/script تولید می‌کنند. سیاست اینجا متعادل است:
 * `script-src` با `unsafe-inline` ولی `frame-ancestors 'none'` و
 * `object-src 'none'` که مهم‌ترین بردارهای حمله را می‌بندد. سخت‌گیرترکردن
 * آن نیازمند nonce برای همه‌ی اسکریپت‌هاست که در فاز بعد انجام می‌شود.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // جلوگیری از clickjacking — این سایت هرگز نباید در iframe بیاید
            'X-Frame-Options' => 'DENY',

            // جلوگیری از حدس نوع MIME توسط مرورگر
            'X-Content-Type-Options' => 'nosniff',

            // افشای referrer فقط در همان مبدأ
            'Referrer-Policy' => 'strict-origin-when-cross-origin',

            // محدودکردن قابلیت‌های مرورگر — دوربین/میکروفون/موقعیت لازم نیست
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), interest-cohort=()',

            // هیچ‌کس نباید این سایت را در قاب دیگری جاسازی کند
            'X-Permitted-Cross-Domain-Policies' => 'none',

            'Content-Security-Policy' => implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data: blob:",
                "font-src 'self' data:",
                "connect-src 'self'",
                "form-action 'self'",
                "base-uri 'self'",
                "object-src 'none'",
                "frame-ancestors 'none'",
            ]),
        ];

        // HSTS فقط روی HTTPS معنا دارد — ارسال آن روی HTTP بی‌اثر است
        // و در توسعه‌ی محلی می‌تواند مرورگر را قفل کند.
        if ($request->secure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            // پاسخ‌های پنل Filament هدر خودشان را دارند؛ بازنویسی نمی‌کنیم
            // مگر برای مواردی که واقعاً لازم است.
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
