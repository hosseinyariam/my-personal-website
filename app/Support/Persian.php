<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * کمک‌کننده‌های متن فارسی.
 *
 * چرا این‌ها لازم‌اند: نمایش نادرست اعداد و نیم‌فاصله در سایت فارسی
 * آماتور به نظر می‌رسد، و اصل برند «Prestige through precision» است.
 * این توابع جای منطق پراکنده در Blade را می‌گیرند.
 */
final class Persian
{
    /** ارقام لاتین را به ارقام فارسی تبدیل می‌کند. */
    public static function digits(string|int|null $value): string
    {
        if ($value === null) {
            return '';
        }

        return strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }

    /** ارقام فارسی/عربی را به لاتین برمی‌گرداند — برای ورودی فرم. */
    public static function toLatinDigits(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    /**
     * جداساز هزارگان با ارقام فارسی.
     */
    public static function number(int|float|null $value): string
    {
        if ($value === null) {
            return '';
        }

        return self::digits(number_format($value, 0, '.', '٫'));
    }

    /**
     * «۱۲ دقیقه» را از تعداد کلمات می‌سازد.
     *
     * سرعت خواندن فارسی حدود ۲۰۰ کلمه در دقیقه است — کمتر از انگلیسی،
     * چون نیم‌فاصله و کشیدگی، طول مؤثر کلمه را بیشتر می‌کند.
     */
    public static function readingTime(?string $text): string
    {
        $words = self::wordCount($text);
        $minutes = max(1, (int) ceil($words / 200));

        return self::digits($minutes).' دقیقه مطالعه';
    }

    public static function readingTimeMinutes(?string $text): int
    {
        return max(1, (int) ceil(self::wordCount($text) / 200));
    }

    private static function wordCount(?string $text): int
    {
        if (blank($text)) {
            return 0;
        }

        // تگ‌های HTML را حذف و بر فاصله/نیم‌فاصله تقسیم می‌کنیم
        $plain = strip_tags($text);
        $plain = str_replace(["\u{200C}", "\u{200F}", "\u{200E}"], ' ', $plain);
        $tokens = preg_split('/\s+/u', trim($plain)) ?: [];

        return count(array_filter($tokens, fn (string $t): bool => $t !== ''));
    }

    /**
     * تاریخ شمسی خوانا.
     *
     * از Intl استفاده می‌کند که روی هاست نصب است (الزام Runbook:
     * افزونه‌ی `intl`). بدون آن، فرمت دستی خطاپذیر می‌شود.
     */
    public static function date(?\DateTimeInterface $date, string $format = 'd MMMM y'): string
    {
        if ($date === null) {
            return '';
        }

        static $formatter = null;

        if ($formatter === null) {
            $formatter = new \IntlDateFormatter(
                'fa_IR@calendar=persian',
                \IntlDateFormatter::FULL,
                \IntlDateFormatter::NONE,
                'Asia/Tehran',
                \IntlDateFormatter::TRADITIONAL,
                $format
            );
        }

        $formatter->setPattern($format);

        return (string) $formatter->format($date);
    }

    /** بازه‌ی تاریخ با متن مناسب برای «تاکنون». */
    public static function dateRange(
        ?\DateTimeInterface $start,
        ?\DateTimeInterface $end,
        bool $isCurrent = false,
        string $fallback = 'تاریخ نامشخص'
    ): string {
        if ($start === null && $end === null) {
            return $fallback;
        }

        $from = $start ? self::date($start, 'MMMM y') : $fallback;

        if ($isCurrent) {
            return $from.' — تاکنون';
        }

        if ($end === null) {
            return $from;
        }

        return $from.' — '.self::date($end, 'MMMM y');
    }

    /**
     * خلاصه‌ی امن برای جایی که متن بلند جا نمی‌شود.
     */
    public static function excerpt(?string $html, int $characters = 160): string
    {
        if (blank($html)) {
            return '';
        }

        return Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? ''), $characters);
    }

    /**
     * بررسی می‌کند متن شامل حروف فارسی/عربی است یا نه.
     * برای تصمیم‌گیری درباره‌ی `dir` روی مقادیر مختلط.
     */
    public static function isRtl(string $text): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
    }
}
