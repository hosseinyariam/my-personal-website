<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * کانال ارتباطی — نوع و هدف دو مفهوم جدا هستند.
 *
 * CMS:231-243: فقط کانال‌هایی که هم `is_active` و هم `is_public` هستند
 * در سایت عمومی نمایش داده می‌شوند. کنترل در Query انجام می‌شود نه در Blade.
 */
enum ContactChannelType: string
{
    case Email = 'email';
    case Website = 'website';
    case Social = 'social';
    case Phone = 'phone';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'ایمیل',
            self::Website => 'وب‌سایت',
            self::Social => 'شبکه اجتماعی',
            self::Phone => 'تلفن',
            self::Other => 'سایر',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c): array => [$c->value => $c->label()])
            ->all();
    }
}
