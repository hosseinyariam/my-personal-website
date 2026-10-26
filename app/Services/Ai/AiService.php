<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\AiProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * سرویس مرکزی هوش مصنوعی.
 *
 * ── اصولی که این سرویس تضمین می‌کند ──
 *
 * ۱. **یک نقطه‌ی ورود.** معماری:811: AI نباید در هر ماژول جداگانه
 *    پیاده‌سازی شود. هر مصرف‌کننده از اینجا رد می‌شود.
 *
 * ۲. **کلید هرگز بیرون نمی‌رود.** کلید فقط در `OpenAiCompatibleAdapter`
 *    و در لحظه‌ی درخواست رمزگشایی می‌شود. هیچ متدی از این کلاس
 *    کلید را برنمی‌گرداند و هیچ خطایی آن را شامل نمی‌شود.
 *
 * ۳. **غیرفعال‌بودن یک حالت معتبر است.** بدون تنظیمات، `NullAdapter`
 *    بی‌صدا کار می‌کند. هیچ شرط پراکنده‌ی `if (ai enabled)` لازم نیست.
 *
 * ۴. **AI هرگز منتشر نمی‌کند.** خروجی فقط پیشنهاد است و باید
 *    توسط انسان پذیرفته شود. اصل Human-in-the-loop (BIM:37-38).
 * ───────────────────────────────────────────
 */
class AiService
{
    /** سقف درخواست در دقیقه — محافظت از هزینه و مصرف (معماری:853). */
    private const RATE_LIMIT_PER_MINUTE = 10;

    /**
     * ارسال درخواست به فراهم‌کننده‌ی فعال.
     *
     * @param  array<string, mixed>  $options
     * @return array{ok: bool, text: string|null, error: string|null, usage: array<string, mixed>|null}
     */
    public function complete(string $prompt, array $options = []): array
    {
        // غیرفعال‌بودن کل قابلیت — قبل از هر کار دیگری بررسی می‌شود
        if (! config('services.ai.enabled', false)) {
            return $this->notAvailable('قابلیت هوش مصنوعی در تنظیمات سایت غیرفعال است.');
        }

        $provider = AiProvider::default();

        if ($provider === null) {
            return $this->notAvailable('هیچ فراهم‌کننده‌ی هوش مصنوعی فعالی تنظیم نشده است.');
        }

        // Rate limit: بدون آن، یک حلقه‌ی ناخواسته هزینه‌ی واقعی می‌سازد.
        if (! $this->withinRateLimit()) {
            return $this->notAvailable('تعداد درخواست‌ها بیش از حد مجاز است. کمی بعد تلاش کنید.');
        }

        $adapter = $this->adapterFor($provider);

        if (! $adapter->isReady()) {
            return $this->notAvailable('فراهم‌کننده آماده نیست: کلید یا آدرس تنظیم نشده است.');
        }

        return $adapter->complete($prompt, $options);
    }

    /**
     * آیا قابلیت AI در دسترس است؟
     *
     * برای اینکه پنل بتواند دکمه‌های AI را مشروط نشان دهد.
     */
    public function isAvailable(): bool
    {
        if (! config('services.ai.enabled', false)) {
            return false;
        }

        $provider = AiProvider::default();

        return $provider !== null && $this->adapterFor($provider)->isReady();
    }

    /**
     * نام فراهم‌کننده‌ی فعال برای نمایش در پنل.
     */
    public function activeProviderName(): string
    {
        $provider = AiProvider::default();

        if ($provider === null) {
            return 'غیرفعال';
        }

        return $this->adapterFor($provider)->name();
    }

    /**
     * تنظیم فراهم‌کننده‌ی پیش‌فرض، با تضمین «فقط یکی».
     *
     * Blueprint:567-569: فقط یک رکورد می‌تواند `is_default` باشد.
     * این تضمین در تراکنش انجام می‌شود چون دو درخواست هم‌زمان
     * می‌توانند هر دو `is_default=true` بنویسند.
     */
    public function setDefault(AiProvider $provider): void
    {
        DB::transaction(function () use ($provider): void {
            AiProvider::query()
                ->whereKeyNot($provider->getKey())
                ->where('is_default', true)
                ->update(['is_default' => false]);

            $provider->forceFill(['is_default' => true])->save();
        });
    }

    /**
     * تست اتصال از سمت سرور.
     *
     * Blueprint:263-264: تست اتصال باید سمت سرور باشد و خطایش نباید
     * راز را لو بدهد. نتیجه فقط «موفق/ناموفق + پیام عمومی» است.
     *
     * @return array{ok: bool, message: string}
     */
    public function testConnection(AiProvider $provider): array
    {
        $adapter = $this->adapterFor($provider);

        if (! $adapter->isReady()) {
            return ['ok' => false, 'message' => 'کلید یا آدرس فراهم‌کننده تنظیم نشده است.'];
        }

        $result = $adapter->complete('پاسخ کوتاه: تنها کلمه «سلام» را بنویس.');

        if ($result['ok']) {
            return ['ok' => true, 'message' => 'اتصال برقرار شد.'];
        }

        // پیام خطای آداپتر عمداً عمومی است و شامل جزئیات درخواست
        // یا کلید نمی‌شود.
        return ['ok' => false, 'message' => $result['error'] ?? 'اتصال برقرار نشد.'];
    }

    private function adapterFor(AiProvider $provider): ProviderAdapter
    {
        return match ($provider->provider_key) {
            'openai', 'openai_compatible', 'openrouter', 'groq' => new OpenAiCompatibleAdapter($provider),
            default => new NullAdapter,
        };
    }

    private function withinRateLimit(): bool
    {
        $key = 'ai:requests:'.(auth()->id() ?? 'guest');

        if (RateLimiter::tooManyAttempts($key, self::RATE_LIMIT_PER_MINUTE)) {
            Log::warning('درخواست هوش مصنوعی به سقف نرخ خورد.', ['key' => $key]);

            return false;
        }

        RateLimiter::hit($key, 60);

        return true;
    }

    /**
     * @return array{ok: false, text: null, error: string, usage: null}
     */
    private function notAvailable(string $message): array
    {
        return [
            'ok' => false,
            'text' => null,
            'error' => $message,
            'usage' => null,
        ];
    }
}
