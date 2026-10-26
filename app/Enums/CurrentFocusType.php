<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * نوع تمرکز فعلی.
 *
 * IA:250-255 چهار بخش را صریح نام می‌برد: در حال ساخت، در حال یادگیری،
 * Explore، هدف فعلی. همین چهار مورد اینجا enum شده‌اند.
 */
enum CurrentFocusType: string
{
    case Building = 'building';
    case Learning = 'learning';
    case Exploring = 'exploring';
    case Goal = 'goal';

    public function label(): string
    {
        return match ($this) {
            self::Building => 'در حال ساخت',
            self::Learning => 'در حال یادگیری',
            self::Exploring => 'در حال کشف',
            self::Goal => 'هدف فعلی',
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
