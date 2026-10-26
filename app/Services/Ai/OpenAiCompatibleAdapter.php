<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Models\AiProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * آداپتر سازگار با OpenAI.
 *
 * ── محافظت SSRF — چرا این کلاس یک لایه‌ی جدا دارد ──
 * دستورالعمل بخش ۱۰ صریحاً محافظت SSRF را الزام کرده، ولی **هیچ‌کدام
 * از ۱۱ سند** به آن اشاره نکرده — این یک شکاف مستنداتی است. صورت
 * مسئله واقعی است: مدیر می‌تواند `base_url` را آزادانه وارد کند و
 * سرور به آن درخواست می‌زند. بدون محافظت، این یک SSRF کلاسیک است:
 * مدیر (یا کسی که دسترسی پنل را گرفته) می‌تواند سرور را وادار کند
 * به سرویس‌های داخلی هاست درخواست بزند.
 *
 * کاهش‌دهنده‌ها:
 *   ۱. فقط طرح `https` پذیرفته می‌شود (بدون http ساده)
 *   ۲. آدرس باید دامنه‌ی عمومی باشد — آدرس‌های داخلی/خصوصی رد می‌شوند
 *   ۳. ریدایرکت غیرفعال است — وگرنه یک آدرس عمومی می‌تواند به داخلی ریدایرکت کند
 *   ۴. Timeout سخت — جلوگیری از hang کردن worker
 * ───────────────────────────────────────────────────────
 */
class OpenAiCompatibleAdapter implements ProviderAdapter
{
    /** Timeout به ثانیه — روی هاست اشتراکی نباید بی‌نهایت منتظر ماند. */
    private const TIMEOUT_SECONDS = 30;

    public function __construct(
        private readonly AiProvider $provider
    ) {}

    public function complete(string $prompt, array $options = []): array
    {
        if (! $this->isReady()) {
            return [
                'ok' => false,
                'text' => null,
                'error' => 'فراهم‌کننده آماده نیست: کلید تنظیم نشده یا غیرفعال است.',
                'usage' => null,
            ];
        }

        $url = $this->endpointUrl();

        if ($url === null) {
            return [
                'ok' => false,
                'text' => null,
                'error' => 'آدرس فراهم‌کننده نامعتبر است. آدرس باید یک دامنه‌ی عمومی با طرح https باشد.',
                'usage' => null,
            ];
        }

        try {
            $payload = array_filter([
                'model' => $this->provider->model_name,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $options['temperature'] ?? 0.4,
                'max_tokens' => $options['max_tokens'] ?? 700,
            ], fn ($v): bool => $v !== null);

            $response = Http::withToken((string) $this->provider->api_key)
                ->timeout(self::TIMEOUT_SECONDS)
                ->connectTimeout(10)
                // ریدایرکت غیرفعال: یک دامنه‌ی مجاز می‌تواند به یک آدرس
                // داخلی ریدایرکت کند و محافظت دامنه را دور بزند.
                ->withoutRedirecting()
                ->acceptJson()
                ->post($url, $payload);

            if (! $response->successful()) {
                // متن پاسخ ارائه‌دهنده ممکن است شامل بخشی از کلید باشد،
                // پس فقط کد وضعیت ثبت می‌شود نه بدنه.
                Log::warning('درخواست هوش مصنوعی ناموفق بود.', [
                    'provider_id' => $this->provider->getKey(),
                    'status' => $response->status(),
                ]);

                return [
                    'ok' => false,
                    'text' => null,
                    'error' => 'ارائه‌دهنده با خطا پاسخ داد (کد '.$response->status().').',
                    'usage' => null,
                ];
            }

            $data = $response->json();

            return [
                'ok' => true,
                'text' => $data['choices'][0]['message']['content'] ?? null,
                'error' => null,
                'usage' => $data['usage'] ?? null,
            ];
        } catch (\Throwable $e) {
            // پیام خطا پاک‌سازی می‌شود: استثنای HTTP ممکن است URL کامل
            // یا هدرهای درخواست را در متن داشته باشد.
            Log::error('اتصال به فراهم‌کننده هوش مصنوعی شکست خورد.', [
                'provider_id' => $this->provider->getKey(),
                'exception' => $e::class,
            ]);

            return [
                'ok' => false,
                'text' => null,
                'error' => 'اتصال به ارائه‌دهنده برقرار نشد.',
                'usage' => null,
            ];
        }
    }

    public function isReady(): bool
    {
        return $this->provider->is_enabled && $this->provider->hasKey();
    }

    public function name(): string
    {
        return (string) ($this->provider->name ?: $this->provider->provider_key);
    }

    /**
     * ساخت و اعتبارسنجی آدرس endpoint.
     *
     * @return string|null  `null` یعنی آدرس نامعتبر است
     */
    private function endpointUrl(): ?string
    {
        $base = trim((string) $this->provider->base_url);

        if ($base === '') {
            return null;
        }

        if (! SsrfGuard::isSafeUrl($base)) {
            return null;
        }

        // مسیر پیش‌فرض سازگار با OpenAI، اگر مدیر مسیر کامل نداده باشد
        $trimmed = rtrim($base, '/');

        if (! str_contains($trimmed, '/chat/completions')) {
            $trimmed .= '/chat/completions';
        }

        return $trimmed;
    }
}
