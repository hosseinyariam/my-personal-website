<?php

declare(strict_types=1);

namespace App\Enums;

/** وضعیت تمرکز فعلی — طبق Blueprint:441 */
enum CurrentFocusStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'فعال',
            self::Paused => 'متوقف‌شده',
            self::Completed => 'تمام‌شده',
            self::Archived => 'آرشیو',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'badge-success',
            self::Paused => 'badge-warning',
            self::Completed => 'badge-info',
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
