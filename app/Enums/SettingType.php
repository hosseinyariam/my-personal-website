<?php

declare(strict_types=1);

namespace App\Enums;

/** نوع مقدار تنظیمات — کنترل type-safe طبق CMS:245-251 */
enum SettingType: string
{
    case String = 'string';
    case Boolean = 'boolean';
    case Integer = 'integer';
    case Json = 'json';

    public function label(): string
    {
        return match ($this) {
            self::String => 'متن',
            self::Boolean => 'بولی',
            self::Integer => 'عدد',
            self::Json => 'JSON',
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
