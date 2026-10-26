<?php

declare(strict_types=1);

namespace App\Enums;

/** نوع همکاری در سابقه‌ی کاری — طبق Blueprint:316 */
enum EmploymentType: string
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contract = 'contract';
    case Freelance = 'freelance';
    case Internship = 'internship';
    case Volunteer = 'volunteer';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'تمام‌وقت',
            self::PartTime => 'پاره‌وقت',
            self::Contract => 'قراردادی',
            self::Freelance => 'فریلنس',
            self::Internship => 'کارآموزی',
            self::Volunteer => 'داوطلبانه',
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
