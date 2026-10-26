<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * وضعیت محصول — جدا از وضعیت پروژه.
 *
 * اصل IA: Project ≠ Product. یک محصول می‌تواند چند پروژه داشته باشد.
 * چرخه‌ی محصول به «عرضه به بازار» اشاره دارد، نه به «کار فنی».
 */
enum ProductStatus: string
{
    case Idea = 'idea';
    case Development = 'development';
    case Beta = 'beta';
    case Live = 'live';
    case Paused = 'paused';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Idea => 'ایده',
            self::Development => 'در حال توسعه',
            self::Beta => 'آزمایشی',
            self::Live => 'فعال',
            self::Paused => 'متوقف‌شده',
            self::Archived => 'آرشیو',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Idea => 'badge-neutral',
            self::Development => 'badge-info',
            self::Beta => 'badge-accent',
            self::Live => 'badge-success',
            self::Paused => 'badge-warning',
            self::Archived => 'badge-neutral',
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
