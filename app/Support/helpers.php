<?php

declare(strict_types=1);

if (! function_exists('fa_digits')) {
    /**
     * تبدیل ارقام لاتین به فارسی — الزام «ارقام فارسی در متن فارسی» (بخش ۷-ج سند).
     *
     * فقط برای متنِ نمایشی به‌کار می‌رود؛ هرگز برای مقادیر ماشین‌خوان
     * (URL، مقدار فرم، datetime، شناسه) — آن‌ها باید لاتین بمانند.
     */
    function fa_digits(string|int|float|null $value): string
    {
        return strtr((string) $value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
    }
}

if (! function_exists('fa_date')) {
    /**
     * تاریخ شمسی خوانا — مثل «۱۲ آبان ۱۴۰۳».
     *
     * از تقویم فارسیِ ext-intl استفاده می‌کند؛ اگر افزونه در دسترس نبود
     * به تاریخ میلادیِ فارسی‌شده بازمی‌گردد تا صفحه هرگز نشکند.
     */
    function fa_date(\DateTimeInterface|string|null $date, string $pattern = 'd MMMM y'): string
    {
        if ($date === null) {
            return '';
        }

        $date = $date instanceof \DateTimeInterface ? $date : new \DateTimeImmutable($date);

        if (! class_exists(\IntlDateFormatter::class)) {
            return fa_digits($date->format('Y/m/d'));
        }

        $formatter = new \IntlDateFormatter(
            'fa_IR@calendar=persian',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            config('app.timezone'),
            \IntlDateFormatter::TRADITIONAL,
            $pattern,
        );

        return $formatter->format($date) ?: fa_digits($date->format('Y/m/d'));
    }
}
