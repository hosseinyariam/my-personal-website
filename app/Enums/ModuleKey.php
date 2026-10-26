<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * کلید ماژول‌ها — تصمیم D7: ۱۲ ماژول طبق IA و معماری.
 *
 * چرا enum و نه فقط رکورد دیتابیس: کد باید بتواند به یک ماژول *اشاره* کند
 * (middleware، ناوبری، کنترل مسیر) بدون Query. جدول `modules` وضعیت
 * روشن/خاموش و ترتیب را نگه می‌دارد؛ این enum فهرست پایدار کلیدهاست.
 */
enum ModuleKey: string
{
    case Biography = 'biography';
    case Projects = 'projects';
    case Products = 'products';
    case Companies = 'companies';
    case Notes = 'notes';
    case Timeline = 'timeline';
    case CurrentFocus = 'current_focus';
    case Skills = 'skills';
    case Education = 'education';
    case Achievements = 'achievements';
    case Certificates = 'certificates';
    case Learning = 'learning';

    public function label(): string
    {
        return match ($this) {
            self::Biography => 'بیوگرافی',
            self::Projects => 'پروژه‌ها',
            self::Products => 'محصولات',
            self::Companies => 'شرکت‌ها',
            self::Notes => 'یادداشت‌ها',
            self::Timeline => 'مسیر حرفه‌ای',
            self::CurrentFocus => 'تمرکز فعلی',
            self::Skills => 'مهارت‌ها',
            self::Education => 'تحصیلات',
            self::Achievements => 'دستاوردها',
            self::Certificates => 'گواهی‌نامه‌ها',
            self::Learning => 'یادگیری',
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
