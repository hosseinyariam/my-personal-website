<?php

declare(strict_types=1);

namespace App\Enums;

/** نوع رویداد مسیر حرفه‌ای — طبق Blueprint:418 */
enum TimelineEventType: string
{
    case Work = 'work';
    case Launch = 'launch';
    case Learning = 'learning';
    case Milestone = 'milestone';
    case Recognition = 'recognition';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Work => 'کار',
            self::Launch => 'عرضه',
            self::Learning => 'یادگیری',
            self::Milestone => 'نقطه عطف',
            self::Recognition => 'بازشناسی',
            self::Other => 'سایر',
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
