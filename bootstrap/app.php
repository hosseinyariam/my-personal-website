<?php

declare(strict_types=1);

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // پشته‌ی سراسری، نه گروه web: پاسخ‌های خطا (۴۰۴/۵۰۰) توسط
        // مدیرِ استثنا رندر می‌شوند و میان‌افزار گروه web را رد می‌کنند —
        // بدون این، صفحات خطا بدون هدرهای امنیتی سرو می‌شدند.
        $middleware->append(SecurityHeaders::class);

        // پنل پشت پراکسیِ هاست اشتراکی — تشخیص درست HTTPS
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
