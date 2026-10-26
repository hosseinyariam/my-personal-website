<?php

declare(strict_types=1);

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;

/**
 * آواتار محلی — بدون هیچ درخواست خارجی.
 *
 * ── چرا این کلاس لازم است ──
 * پیش‌فرض Filament یعنی `UiAvatarsProvider`، تصویر را از سرویس
 * `ui-avatars.com` می‌سازد. این دو مشکل دارد:
 *
 *   ۱. **وابستگی خارجی.** مستندات پروژه صریحاً می‌گویند «وابستگی
 *      غیرضروری به سرویس‌های خارجی ایجاد نکن». یک درخواست به سرور
 *      ثالث در هر بار باز کردن پنل، هم کند است و هم در ایران ممکن
 *      است به‌کل در دسترس نباشد و آواتار شکسته بماند.
 *
 *   ۲. **افشای نام.** نام کامل کاربر در URL سرویس خارجی فرستاده
 *      می‌شود؛ یعنی نام مدیر سایت به یک سرور ناشناس می‌رود. این
 *      با اصل حداقل‌افشا ناسازگار است.
 *
 * ── راه‌حل ──
 * یک Data URI می‌سازیم که حرف اول نام را در یک مربع رنگی نشان
 * می‌دهد. مزیت: صفر درخواست شبکه، رندر فوری، و کار می‌کند حتی اگر
 * شبکه قطع باشد. چون SVG داخلی است و با base64 کد می‌شود، هیچ
 * خطر XSS ندارد (محتوای کاربر به‌صورت داده‌ی کدشده می‌رود، نه HTML).
 */
class LocalInitialsAvatarProvider implements AvatarProvider
{
    /**
     * آدرس آواتار برای یک رکورد.
     *
     * الگو: SVG با حرف اول نام روی پس‌زمینه‌ی رنگ لهجه‌ی برند.
     */
    public function get(Model $record): string
    {
        $name = (string) ($record->getAttribute('name') ?? '');
        $initial = $this->initial($name);
        $background = '#2563EB'; // --color-accent از دیزاین‌سیستم
        $foreground = '#FFFFFF';

        // SVG ساده و کوچک — بدون فونت خارجی. `font-family: sans-serif`
        // باعث می‌شود حرف به فونت سیستم رندر شود و هیچ درخواستی نرود.
        $svg = <<<SVG
            <svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">
                <rect width="128" height="128" rx="64" fill="{$background}"/>
                <text x="64" y="64" fill="{$foreground}" font-family="sans-serif" font-size="56" font-weight="600" text-anchor="middle" dominant-baseline="central">{$initial}</text>
            </svg>
            SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * حرف اول نام — با پشتیبانی درست از فارسی.
     *
     * `mb_substr` لازم است چون نام فارسی چندبایتی است و `substr` ساده
     * اولین بایت را می‌بُرد و کاراکتر خراب تولید می‌کند.
     */
    private function initial(string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            return '؟';
        }

        $char = mb_substr($name, 0, 1, 'UTF-8');

        // اگر کاراکتر غیرقابل‌نمایش بود، جایگزین امن بده تا SVG نشکند
        return preg_match('/[\p{L}\p{N}]/u', $char) === 1 ? $char : '؟';
    }
}
