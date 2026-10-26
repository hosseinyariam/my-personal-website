<?php

declare(strict_types=1);

namespace App\Services\Ai;

/**
 * قرارداد آداپتر فراهم‌کننده‌ی هوش مصنوعی.
 *
 * معماری:809-823: AI باید از یک Adapter رد شود تا فراهم‌کننده قابل
 * تعویض باشد. AI نباید در هر ماژول جداگانه پیاده‌سازی شود — چون
 * آنوقت کلید API، مدیریت خطا و rate limit در چند جا تکرار می‌شود
 * و یکی از آن‌ها دیر یا زود ناامن می‌شود.
 */
interface ProviderAdapter
{
    /**
     * ارسال درخواست و دریافت متن پاسخ.
     *
     * @param  array<string, mixed>  $options
     * @return array{ok: bool, text: string|null, error: string|null, usage: array<string, mixed>|null}
     */
    public function complete(string $prompt, array $options = []): array;

    /** آیا این آداپتر آماده‌ی استفاده است؟ (کلید دارد، فعال است) */
    public function isReady(): bool;

    /** نام نمایشی برای پنل. */
    public function name(): string;
}
