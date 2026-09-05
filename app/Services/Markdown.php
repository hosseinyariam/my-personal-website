<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * رندر امن مارک‌داون برای محتوای پست (US-M6-1).
 *
 * HTML خامِ داخل مارک‌داون حذف می‌شود (html_input: strip) و لینک‌های
 * ناامن غیرفعال می‌گردند. حتی با اینکه تنها نویسنده خودِ مدیر است،
 * این لایه از XSS از مسیر محتوا جلوگیری می‌کند.
 */
final class Markdown
{
    public static function render(?string $markdown): HtmlString
    {
        if ($markdown === null || trim($markdown) === '') {
            return new HtmlString('');
        }

        return new HtmlString(Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]));
    }
}
