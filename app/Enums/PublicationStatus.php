<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * وضعیت انتشار — تنها منبع حقیقت برای «آیا این محتوا عمومی است؟»
 *
 * تصمیم D1: Blueprint بر ERD اولویت گرفت. «آرشیو» و «خصوصی» دو مفهوم
 * متفاوتند و در دو فیلد جدا نگه داشته می‌شوند (status + visibility).
 */
enum PublicationStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'پیش‌نویس',
            self::Published => 'منتشرشده',
            self::Archived => 'آرشیو',
        };
    }

    /** آیا این وضعیت اجازه‌ی نمایش عمومی می‌دهد؟ */
    public function isPubliclyVisible(): bool
    {
        return $this === self::Published;
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'badge-neutral',
            self::Published => 'badge-success',
            self::Archived => 'badge-warning',
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
