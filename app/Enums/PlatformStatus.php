<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * وضعیت پلتفرم — نگاشت مستقیم به نشان چهارحالته‌ی UI Kit.
 */
enum PlatformStatus: string
{
    case Live = 'live';
    case Beta = 'beta';
    case Development = 'development';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Live => 'زنده',
            self::Beta => 'بتا',
            self::Development => 'در توسعه',
            self::Archived => 'آرشیو',
        };
    }

    /** نوع نشان در کیت: brand | line | deep */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Live, self::Beta => 'brand',
            self::Development, self::Archived => 'line',
        };
    }

    /** وضعیت «زنده» با نقطه‌ی تپنده نمایش داده می‌شود، بقیه با آیکون. */
    public function isLive(): bool
    {
        return $this === self::Live;
    }

    public function icon(): ?string
    {
        return match ($this) {
            self::Live => null,
            self::Beta => 'flask',
            self::Development => 'tools',
            self::Archived => 'archive',
        };
    }

    /** @return array<string, string> برای Select پنل */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
