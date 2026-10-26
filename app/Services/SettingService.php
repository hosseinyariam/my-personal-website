<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SettingType;
use App\Models\Setting;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

/**
 * سرویس تنظیمات عمومی.
 *
 * قاعده‌ی امنیتی که اینجا اعمال می‌شود: نام کلیدهای حساس رد می‌شود.
 * فیلد `setting.value` در دیتابیس متنی است و متنی ماندن آن یعنی هر
 * چیزی می‌تواند در آن بنشیند — از جمله کلید API اگر کسی اشتباه کند.
 * این سرویس جلوی آن اشتباه را می‌گیرد.
 *
 * جای درست کلیدها: `ai_providers.api_key` با cast رمزنگاری‌شده.
 */
class SettingService
{
    /**
     * الگوهای نام کلیدی که هرگز نباید در `settings` ذخیره شوند.
     *
     * @var list<string>
     */
    private const FORBIDDEN_KEY_PATTERNS = [
        'password',
        'secret',
        'token',
        'api_key',
        'apikey',
        'private_key',
        'credential',
    ];

    /**
     * مقدار یک تنظیم را با نوع درست برمی‌گرداند.
     *
     * نتیجه کش می‌شود چون صفحه‌ی اصلی و Header در هر درخواست چند
     * تنظیم می‌خوانند. کش در مدل Setting باطل می‌شود.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->allPublicValues();
        $setting = $settings[$key] ?? null;

        // تنظیم غیرعمومی هم قابل خواندن است (مثلServe در پنل)، ولی
        // چون `allPublicValues` فقط عمومی‌ها را می‌آورد، اینجا مستقیم
        // از دیتابیس می‌خوانیم.
        if ($setting === null) {
            $model = Setting::query()->where('key', $key)->first();

            return $model?->typedValue() ?? $default;
        }

        return $setting['value'] ?? $default;
    }

    /**
     * تنظیمات عمومی — برای استفاده در نماهای عمومی.
     *
     * @return array<string, mixed>
     */
    public function publicValues(): array
    {
        return collect($this->allPublicValues())
            ->map(fn (array $row): mixed => $row['value'])
            ->all();
    }

    /**
     * ذخیره یا به‌روزرسانی یک تنظیم.
     *
     * @throws \InvalidArgumentException اگر نام کلید حساس باشد
     */
    public function set(string $key, mixed $value, SettingType $type = SettingType::String, ?string $group = null, bool $isPublic = false): Setting
    {
        $this->assertKeyIsSafe($key);

        $stored = match ($type) {
            SettingType::Boolean => $value ? '1' : '0',
            SettingType::Integer => (string) (int) $value,
            SettingType::Json => json_encode($value, JSON_UNESCAPED_UNICODE),
            SettingType::String => (string) $value,
        };

        $setting = Setting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $stored,
                'type' => $type,
                'group' => $group,
                'is_public' => $isPublic,
            ]
        );

        // کش باید باطل شود تا مقدار کهنه نماند. مدل خودش هم باطل
        // می‌کند، ولی اینجا صریح است تا اگر روزی مدل تغییر کرد،
        // رفتار سرویس تضمین‌شده بماند.
        Cache::forget(CacheKeys::PUBLIC_SETTINGS);

        return $setting;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function assertKeyIsSafe(string $key): void
    {
        $lower = mb_strtolower($key);

        foreach (self::FORBIDDEN_KEY_PATTERNS as $pattern) {
            if (str_contains($lower, $pattern)) {
                throw new \InvalidArgumentException(
                    "کلید «{$key}» نباید در جدول تنظیمات ذخیره شود — این جدول راز نگه نمی‌دارد. "
                    .'کلیدهای حساس باید در `ai_providers` با رمزنگاری ذخیره شوند.'
                );
            }
        }
    }

    /**
     * @return array<string, array{value: mixed, type: string}>
     */
    private function allPublicValues(): array
    {
        return Cache::remember(
            CacheKeys::PUBLIC_SETTINGS,
            now()->addHours(6),
            fn (): array => Setting::query()
                ->public()
                ->get()
                ->mapWithKeys(fn (Setting $s): array => [
                    (string) $s->key => [
                        'value' => $s->typedValue(),
                        'type' => $s->type->value,
                    ],
                ])
                ->all()
        );
    }
}
