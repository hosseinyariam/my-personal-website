<?php

declare(strict_types=1);

namespace App\Services\Ai;

/**
 * آداپتر خالی — وقتی هوش مصنوعی تنظیم یا فعال نشده است.
 *
 * ── چرا این کلاس وجود دارد ──
 * مستندات می‌گویند AI در MVP غیرفعال است (CMS:255-256 و Blueprint:775-777).
 * اما کد ماژول‌ها باید بتواند بدون شرط‌های پراکنده صدا زده شود.
 * با این آداپتر، `AiService` همیشه پاسخ می‌دهد: «آماده نیستم»،
 * بدون اینکه درخواستی به خارج برود یا خطایی پرتاب شود.
 *
 * Blueprint:876 هم الزام کرده «رفتار در حالت نبود تنظیمات یا قطع سرویس»
 * تست شود. این کلاس همان حالت است.
 */
class NullAdapter implements ProviderAdapter
{
    public function complete(string $prompt, array $options = []): array
    {
        return [
            'ok' => false,
            'text' => null,
            'error' => 'قابلیت هوش مصنوعی فعال نیست. برای فعال‌سازی، تنظیمات فراهم‌کننده را در پنل وارد کنید.',
            'usage' => null,
        ];
    }

    public function isReady(): bool
    {
        return false;
    }

    public function name(): string
    {
        return 'غیرفعال';
    }
}
