<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * ساخت اسلاگ لاتینِ یکتا از عنوان فارسی (تصمیم D16).
 *
 * چرا ترانویسی و نه اسلاگ فارسی؟ اسلاگ لاتین در URL انکد نمی‌شود،
 * در اشتراک‌گذاری نمی‌شکند و مسیر نسخه‌ی چندزبانه‌ی آینده را باز نگه می‌دارد.
 */
final class Slugger
{
    /**
     * نگاشت حرف‌به‌حرف فارسی/عربی به لاتین.
     *
     * @var array<string, string>
     */
    private const MAP = [
        'آ' => 'a', 'ا' => 'a', 'أ' => 'a', 'إ' => 'e', 'ب' => 'b', 'پ' => 'p',
        'ت' => 't', 'ث' => 's', 'ج' => 'j', 'چ' => 'ch', 'ح' => 'h', 'خ' => 'kh',
        'د' => 'd', 'ذ' => 'z', 'ر' => 'r', 'ز' => 'z', 'ژ' => 'zh', 'س' => 's',
        'ش' => 'sh', 'ص' => 's', 'ض' => 'z', 'ط' => 't', 'ظ' => 'z', 'ع' => 'a',
        'غ' => 'gh', 'ف' => 'f', 'ق' => 'gh', 'ک' => 'k', 'ك' => 'k', 'گ' => 'g',
        'ل' => 'l', 'م' => 'm', 'ن' => 'n', 'و' => 'v', 'ه' => 'h', 'ة' => 'h',
        'ی' => 'y', 'ي' => 'y', 'ى' => 'y', 'ئ' => 'y', 'ؤ' => 'o',
        '‌' => '-', 'ء' => '',
        // ارقام فارسی و عربی
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        // اعراب و کشیده — حذف می‌شوند
        'َ' => '', 'ُ' => '', 'ِ' => '', 'ً' => '', 'ٌ' => '', 'ٍ' => '',
        'ّ' => '', 'ْ' => '', 'ٔ' => '', 'ـ' => '',
    ];

    /** ترانویسی + kebab-case. خروجی همیشه غیرخالی است. */
    public static function make(string $value): string
    {
        $slug = Str::slug(strtr($value, self::MAP));

        return $slug !== '' ? $slug : 'item-'.Str::lower(Str::random(6));
    }

    /**
     * همان make(...) اما یکتایی را در جدول مدل تضمین می‌کند.
     * رکورد در حال ویرایش از بررسی تکرار کنار گذاشته می‌شود.
     */
    public static function unique(string $value, string $modelClass, ?int $ignoreId = null, string $column = 'slug'): string
    {
        $base = self::make($value);
        $slug = $base;
        $suffix = 2;

        /** @var class-string<Model> $modelClass */
        while (self::exists($modelClass, $column, $slug, $ignoreId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /** @param class-string<Model> $modelClass */
    private static function exists(string $modelClass, string $column, string $slug, ?int $ignoreId): bool
    {
        $query = $modelClass::query()->where($column, $slug);

        // پیش‌نویس‌های حذف‌شده هم اسلاگ را اشغال می‌کنند (unique در سطح دیتابیس)
        if (method_exists($modelClass, 'withTrashed')) {
            $query->withTrashed();
        }

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
