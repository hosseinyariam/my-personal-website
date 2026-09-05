<?php

declare(strict_types=1);

namespace App\Enums;

enum SkillCategory: string
{
    case Product = 'product';
    case Design = 'design';
    case Technical = 'technical';

    public function label(): string
    {
        return match ($this) {
            self::Product => 'محصول',
            self::Design => 'طراحی',
            self::Technical => 'فنی',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
