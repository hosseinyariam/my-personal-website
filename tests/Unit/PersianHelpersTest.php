<?php

declare(strict_types=1);

it('ارقام لاتین را فارسی می‌کند', function () {
    expect(fa_digits('1403'))->toBe('۱۴۰۳')
        ->and(fa_digits(5))->toBe('۵')
        ->and(fa_digits(null))->toBe('');
});

it('حروف غیرعددی را دست نمی‌زند', function () {
    expect(fa_digits('v1.0.0'))->toBe('v۱.۰.۰');
});

it('تاریخ را به تقویم شمسی می‌برد', function () {
    expect(fa_date('2024-11-02'))->toBe('۱۲ آبان ۱۴۰۳')
        ->and(fa_date(null))->toBe('');
})->skip(fn () => ! class_exists(IntlDateFormatter::class), 'به افزونه‌ی intl نیاز دارد');
