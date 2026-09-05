<?php

declare(strict_types=1);

use App\Services\ReadingTime;

it('حداقل یک دقیقه برمی‌گرداند', function () {
    expect(ReadingTime::forMarkdown(null))->toBe(1)
        ->and(ReadingTime::forMarkdown(''))->toBe(1)
        ->and(ReadingTime::forMarkdown('یک کلمه'))->toBe(1);
});

it('بر پایه‌ی ۱۸۰ کلمه در دقیقه محاسبه می‌کند', function () {
    expect(ReadingTime::forMarkdown(str_repeat('کلمه ', 180)))->toBe(1)
        ->and(ReadingTime::forMarkdown(str_repeat('کلمه ', 181)))->toBe(2)
        ->and(ReadingTime::forMarkdown(str_repeat('کلمه ', 540)))->toBe(3);
});

it('بلوک کد را جزو متن نمی‌شمارد', function () {
    $prose = str_repeat('کلمه ', 180);
    $code = "\n\n```php\n".str_repeat("echo 'x';\n", 200)."```\n";

    // بدون حذف بلوک کد، این عدد به‌شدت بزرگ‌تر می‌شد
    expect(ReadingTime::forMarkdown($prose.$code))->toBe(1);
});

it('نیم‌فاصله را مرز کلمه می‌گیرد', function () {
    // «می‌خواهم» یک کلمه است، نه دو
    expect(ReadingTime::forMarkdown('می‌خواهم'))->toBe(1);
});
