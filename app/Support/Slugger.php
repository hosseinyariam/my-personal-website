<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * ساخت Slug یکتا برای محتوای فارسی.
 *
 * ── تصمیم مهم (D16) ─────────────────────────────────────────────
 * `Str::slug()` متن فارسی را نویسه‌گردانی لاتین می‌کند و نتیجه‌اش
 * کیفیت پایینی دارد: «ویترین» به `oytryn` و «کشاورزی» به `kshaorzy`
 * تبدیل می‌شود. این هم ناخوانا است و هم برای SEO فارسی بی‌ارزش —
 * هیچ‌کس «oytryn» را جست‌وجو نمی‌کند.
 *
 * بنابراین: اگر متن غالباً فارسی باشد، **حروف فارسی در slug حفظ
 * می‌شوند**. URL فارسی معتبر است، مرورگر آن را به‌صورت خوانا نشان
 * می‌دهد و اصل «فارسی‌محور» سند را رعایت می‌کند.
 *
 * برای عنوان‌های لاتین یا مختلط با غلبه‌ی لاتین، مسیر نویسه‌گردانی
 * استاندارد استفاده می‌شود.
 * ────────────────────────────────────────────────────────────────
 */
final class Slugger
{
    /**
     * Slug یکتا می‌سازد و تضمین می‌کند در جدول تکراری نباشد.
     *
     * @param  class-string<Model>  $modelClass
     */
    public static function unique(string $source, string $modelClass, ?int $ignoreId = null, int $maxLength = 180): string
    {
        $base = self::make($source, $maxLength);
        $slug = $base;
        $counter = 1;

        while ($modelClass::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $suffix = '-'.$counter;
            $slug = mb_substr($base, 0, $maxLength - mb_strlen($suffix)).$suffix;
            $counter++;

            // محافظت در برابر حلقه‌ی بی‌پایان در شرایط غیرمنتظره
            if ($counter > 500) {
                $slug = $base.'-'.Str::lower(Str::random(6));
                break;
            }
        }

        return $slug;
    }

    /**
     * Slug پایه از متن — بدون بررسی یگانگی.
     */
    public static function make(string $source, int $maxLength = 180): string
    {
        $source = trim($source);

        if ($source === '') {
            return 'item';
        }

        $slug = self::containsPersian($source)
            ? self::persianSlug($source)      // فارسی را فارسی نگه دار
            : Str::slug($source, '-');        // فقط لاتین را نویسه‌گردانی کن

        if ($slug === '') {
            $slug = 'item-'.Str::lower(Str::random(6));
        }

        return mb_substr($slug, 0, $maxLength);
    }

    /**
     * آیا متن هر حرف فارسی/عربی دارد؟
     *
     * معیار «هر» است نه «اکثریت». چرا: عنوان‌های فنی رایج‌اند که نام
     * لاتین فناوری هم دارند — «ساخت Vinora با Laravel». اگر معیار
     * اکثریت باشد، این عنوان نویسه‌گردانی می‌شود و به
     * `sakht-vinora-ba-laravel` تبدیل می‌شود؛ ناخوانا برای مخاطب فارسی.
     * با معیار «هر»، همان عنوان به «ساخت-Vinora-با-Laravel» می‌رسد که
     * هم خوانا است و هم مرز کلمات لاتین را حفظ می‌کند.
     */
    private static function containsPersian(string $text): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }

    /**
     * تبدیل متن فارسی به بخش معتبر URL با حفظ حروف فارسی.
     *
     * کاراکترهای مجاز: حروف و ارقام فارسی/عربی، حروف لاتین، ارقام لاتین.
     * نیم‌فاصله به خط تیره تبدیل می‌شود (نه حذف) تا مرز کلمات بماند —
     * «پلتفرم‌ها» باید «پلتفرم-ها» شود نه «پلتفرمها».
     */
    private static function persianSlug(string $text): string
    {
        // نیم‌فاصله و فاصله‌های ویژه ← خط تیره
        $text = str_replace(["\u{200C}", "\u{00A0}", "\u{200F}", "\u{200E}"], '-', $text);

        // حذف اعراب و علائم ترکیبی
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text) ?? $text;

        // یکسان‌سازی حروف عربی به فارسی، تا دو نگارش یکسان، یک slug بدهند
        $text = str_replace(['ي', 'ك', 'ة', 'ۀ'], ['ی', 'ک', 'ه', 'ه'], $text);

        // هر چیزی جز حروف/ارقام فارسی و لاتین و خط تیره ← خط تیره
        $text = preg_replace('/[^\p{L}\p{N}\-]+/u', '-', $text) ?? $text;

        // جمع‌کردن خط تیره‌های پشت‌سرهم و حذف از دو سر
        $text = preg_replace('/-+/', '-', $text) ?? $text;

        return trim($text, '-');
    }
}
