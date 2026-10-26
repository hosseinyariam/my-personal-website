<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * محافظت از مسیرهای یک ماژول خاموش.
 *
 * ── چرا ۴۰۴ و نه ۴۰۳ ──
 * CMS:220-223 می‌گوید رفتار URLهای قدیمی ماژول خاموش باید **صریح**
 * انتخاب شود. ما ۴۰۴ انتخاب می‌کنیم، نه ۴۰۳ و نه redirect:
 *
 *   - ۴۰۳ می‌گوید «وجود دارد ولی اجازه نداری» — این وجود یک بخش
 *     خاموش را تأیید می‌کند و اطلاعات ناخواسته می‌دهد.
 *   - redirect به صفحه‌ی دیگر، کاربر را گیج می‌کند و لینک شکسته
 *     را به ظاهر سالم تبدیل می‌کند.
 *   - ۴۰۴ صادقانه است: این بخش الان بخشی از سایت نیست.
 *
 * نکته‌ی امنیتی مهم: خاموش‌کردن ماژول **دسترسی پنل را تغییر نمی‌دهد**.
 * این middleware فقط روی مسیرهای عمومی است (Blueprint:517-519).
 */
class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        if (! Module::isEnabled($moduleKey)) {
            abort(404);
        }

        return $next($request);
    }
}
