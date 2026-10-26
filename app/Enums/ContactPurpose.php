<?php

declare(strict_types=1);

namespace App\Enums;

/** هدف کانال ارتباطی — طبق Blueprint:498 */
enum ContactPurpose: string
{
    case Professional = 'professional';
    case Social = 'social';
    case Support = 'support';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Professional => 'حرفه‌ای',
            self::Social => 'شبکه اجتماعی',
            self::Support => 'پشتیبانی محصول',
            self::General => 'عمومی',
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
