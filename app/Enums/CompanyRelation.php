<?php

declare(strict_types=1);

namespace App\Enums;

/** رابطه‌ی شرکت با حسین — طبق Blueprint:187-188 */
enum CompanyRelation: string
{
    case Founder = 'founder';
    case Employee = 'employee';
    case Collaborator = 'collaborator';
    case Client = 'client';
    case Partner = 'partner';

    public function label(): string
    {
        return match ($this) {
            self::Founder => 'مؤسس',
            self::Employee => 'کارمند',
            self::Collaborator => 'همکار',
            self::Client => 'کارفرما',
            self::Partner => 'شریک',
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
