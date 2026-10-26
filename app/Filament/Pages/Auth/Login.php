<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\Facades\RateLimiter;

/**
 * صفحه‌ی ورود پنل — با محدودیت نرخ متمرکز پروژه.
 *
 * ── چرا این کلاس لازم است ──
 * صفحه‌ی ورود پیش‌فرض Filament درون Livewire خودش `rateLimit(5)` را
 * صدا می‌زند، ولی کلید آن `livewire-rate-limiter:...` است و با
 * تعریف متمرکز پروژه (`admin-login` در `routes/console.php`) یکی
 * نیست. سیاست امنیتی Baseline §6 می‌گوید محدودیت‌ها باید متمرکز
 * تعریف شوند. این کلاس همان limiter نام‌دار را اعمال می‌کند تا یک
 * منبع حقیقت برای «۵ تلاش در دقیقه» بماند.
 *
 * رفتار: اگر سقف رد شود، پیام فارسی throttle نمایش داده می‌شود و
 * ورود انجام نمی‌شود — همان مسیر امنی که Filament برای حالت پیش‌فرض
 * دارد. لایه‌ی Livewire هم دست‌نخورده باقی می‌ماند (دفاع در عمق).
 */
class Login extends \Filament\Pages\Auth\Login
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->applyNamedRateLimiter();
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        return parent::authenticate();
    }

    /**
     * اعمال limiter نام‌دار `admin-login`.
     *
     * اگر آن limiter تعریف نشده باشد، بی‌صدا رد می‌شود و رفتار پیش‌فرض
     * Filament (محدودیت Livewire) برقرار می‌ماند — شکست امن، نه باز.
     *
     * @throws TooManyRequestsException
     */
    protected function applyNamedRateLimiter(): void
    {
        $resolver = RateLimiter::limiter('admin-login');

        if ($resolver === null) {
            return;
        }

        $limit = $resolver(request());
        $key = 'admin-login:'.$limit->key;

        if (RateLimiter::tooManyAttempts($key, $limit->maxAttempts)) {
            throw new TooManyRequestsException(
                static::class,
                'authenticate',
                request()->ip(),
                RateLimiter::availableIn($key),
            );
        }

        RateLimiter::hit($key, $limit->decaySeconds);
    }
}
