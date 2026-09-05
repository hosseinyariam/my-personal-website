<?php

declare(strict_types=1);

namespace App\Services;

/**
 * زمان مطالعه‌ی تخمینی — کلمات ÷ ۱۸۰، حداقل ۱ دقیقه (فاز ۲ سند).
 */
final class ReadingTime
{
    public const WORDS_PER_MINUTE = 180;

    public static function forMarkdown(?string $markdown): int
    {
        if ($markdown === null || trim($markdown) === '') {
            return 1;
        }

        // نشانه‌گذاری مارک‌داون نباید به‌عنوان کلمه شمرده شود
        $text = preg_replace('/```.*?```/su', ' ', $markdown) ?? $markdown;   // بلوک کد
        $text = preg_replace('/`[^`]*`/u', ' ', $text) ?? $text;              // کد درون‌خطی
        $text = preg_replace('/!?\[([^\]]*)\]\([^)]*\)/u', '$1', $text) ?? $text; // لینک و تصویر
        $text = preg_replace('/[#>*_~\-|=]+/u', ' ', $text) ?? $text;         // نشانه‌ها

        $words = preg_split('/[\s\x{200C}]+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return max(1, (int) ceil(count($words) / self::WORDS_PER_MINUTE));
    }
}
