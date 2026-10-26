<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * وضعیت پروژه.
 *
 * توجه: IA صریحاً می‌گوید `Failed / Learned` هم یک وضعیت معتبر است —
 * پروژه‌ی ناموفق که درس داشته، بخشی از هویت «سازنده» است نه لکه‌ی آن.
 * به همین دلیل `archived` داریم ولی برچسبش «متوقف‌شده» است نه «حذف‌شده».
 */
enum ProjectStatus: string
{
    case Idea = 'idea';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Paused = 'paused';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Idea => 'ایده',
            self::InProgress => 'در حال ساخت',
            self::Completed => 'تکمیل‌شده',
            self::Paused => 'متوقف‌شده',
            self::Archived => 'آرشیو',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Idea => 'badge-neutral',
            self::InProgress => 'badge-accent',
            self::Completed => 'badge-success',
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
