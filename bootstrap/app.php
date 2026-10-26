<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureModuleEnabled;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // هدرهای امنیتی روی همه‌ی پاسخ‌ها — وب و پنل
        $middleware->append(SecurityHeaders::class);

        // نام مستعار برای محافظت ماژولی در مسیرها
        $middleware->alias([
            'module' => EnsureModuleEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
        | هیچ اطلاعات محیطی نباید در پاسخ خطا لو برود (دستورالعمل بخش ۱۱).
        | لاراول با APP_DEBUG=false خودش این کار را می‌کند، ولی اینجا
        | برای درخواست‌های JSON و مسیرهای API صریح می‌کنیم تا خطای
        | اعتبارسنجی هم پیام ساختاریافته بدهد نه stack trace.
        */
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson()
        );

        // مسیرهای پنل هرگز نباید در موتور جست‌وجو ایندکس شوند،
        // حتی صفحه‌ی ورود که قابل دسترسی عمومی است.
        $exceptions->respond(function ($response, $exception, Request $request) {
            if (str_starts_with($request->path(), 'admin')) {
                $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            }

            return $response;
        });
    })->create();
