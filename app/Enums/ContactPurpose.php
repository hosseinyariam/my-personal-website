<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * هدف تماس — تصمیم قفل‌شده D10: یک فرم واحد که هر دو پرسونا را پوشش می‌دهد.
 */
enum ContactPurpose: string
{
    case Job = 'job';
    case Service = 'service';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Job => 'فرصت شغلی',
            self::Service => 'درخواست خدمت',
            self::Other => 'سایر',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $c) => [$c->value => $c->label()])->all();
    }
}
