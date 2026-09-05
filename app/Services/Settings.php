<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * دسترسی خوانا به تنظیمات سایت، با کش به‌ازای هر گروه.
 *
 * کلیدهای کش: settings.profile · settings.contact · settings.seo
 * ابطال توسط SettingObserver هنگام ذخیره/حذف (فاز ۲ و ۳ سند).
 */
final class Settings
{
    /** گروه‌های شناخته‌شده و پیش‌فرض‌های ساختاری آن‌ها. */
    public const GROUPS = ['profile', 'contact', 'seo'];

    /** @var array<string, array<string, mixed>> کش درون‌درخواستی */
    private array $memo = [];

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        return data_get($this->group($group), $key, $default);
    }

    /** @return array<string, mixed> */
    public function group(string $group): array
    {
        return $this->memo[$group] ??= Cache::rememberForever(
            self::cacheKey($group),
            fn () => Setting::query()
                ->where('group', $group)
                ->pluck('value', 'key')
                ->all(),
        );
    }

    public function set(string $group, string $key, mixed $value): void
    {
        Setting::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value],
        );

        $this->forget($group);
    }

    public function forget(?string $group = null): void
    {
        foreach ($group ? [$group] : self::GROUPS as $name) {
            Cache::forget(self::cacheKey($name));
            unset($this->memo[$name]);
        }
    }

    public static function cacheKey(string $group): string
    {
        return "settings.{$group}";
    }

    /* ── میان‌برهای پرکاربرد ─────────────────────────────── */

    public function displayName(): string
    {
        return (string) ($this->get('profile', 'display_name') ?: config('app.name'));
    }

    public function notificationEmail(): ?string
    {
        $email = $this->get('contact', 'notification_email');

        return filled($email) ? (string) $email : null;
    }

    /** مسیر ذخیره‌سازی PDF رزومه — دکمه‌ی دانلود فقط با وجود این فایل رندر می‌شود. */
    public function resumePdfPath(): ?string
    {
        $path = $this->get('profile', 'resume_pdf');

        return filled($path) ? (string) $path : null;
    }
}
