<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // فرم تماس: حداکثر ۵ ارسال در ساعت به‌ازای IP (US-M5-2 · D11)
        RateLimiter::for('contact', fn (Request $request) => Limit::perHour(5)
            ->by($request->ip())
            ->response(fn () => back()
                ->withInput()
                ->withErrors(['message' => 'در یک ساعت گذشته چند پیام فرستاده‌اید. کمی بعد دوباره تلاش کنید.'])));

        // ورود به پنل مدیریت
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
    }
}
