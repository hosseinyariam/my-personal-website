<?php

declare(strict_types=1);

namespace App\Enums;

/** نوع یادداشت — طبق Blueprint:372-373 */
enum NoteType: string
{
    case Article = 'article';
    case Note = 'note';
    case CaseStudy = 'case_study';
    case LearningLog = 'learning_log';

    public function label(): string
    {
        return match ($this) {
            self::Article => 'مقاله',
            self::Note => 'یادداشت',
            self::CaseStudy => 'مطالعه موردی',
            self::LearningLog => 'گزارش یادگیری',
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
