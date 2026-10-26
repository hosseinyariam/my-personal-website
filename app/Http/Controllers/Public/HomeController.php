<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\HomepageService;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

/**
 * صفحه اصلی — «Living Profile».
 *
 * ── الزام اصلی ──
 * دستورالعمل: صفحه اصلی باید در حدود ۳۰ ثانیه به سه سؤال پاسخ دهد:
 *   ۱. من چه کسی هستم؟
 *   ۲. چه مسئله‌هایی را دنبال می‌کنم و چه ساخته‌ام؟
 *   ۳. اکنون روی چه چیزی کار می‌کنم؟
 *
 * ترتیب بخش‌ها این ترتیب را دنبال می‌کند: Hero (هویت) → تمرکز فعلی
 * (الان) → کارها → رویکرد → یادداشت‌ها → مسیر → ارتباط.
 *
 * ── حالت خالی ──
 * اگر پروفایل تنظیم نشده باشد، صفحه ۵۰۰ نمی‌دهد. یک صفحه‌ی راه‌اندازی
 * محترمانه نشان داده می‌شود که می‌گوید سایت در حال آماده‌سازی است —
 * نه محتوای ساختگی، نه خطا.
 */
class HomeController extends Controller
{
    public function __invoke(HomepageService $homepage, SeoService $seo): View
    {
        $composition = $homepage->compose();
        $profile = $composition['profile'];

        // صفحه‌ی اصلی همیشه «باید» محتوا داشته باشد؛ اگر پروفایل نیست،
        // نمای راه‌اندازی رندر می‌شود. این حالت واقعی است چون سایت
        // تازه دیپلوی شده و ممکن است محتوا هنوز وارد نشده باشد.
        if ($profile === null) {
            return view('pages.setup', [
                'meta' => $seo->metaFor(
                    'در حال آماده‌سازی',
                    'این سایت در حال آماده‌سازی است.',
                    null,
                    'noindex, nofollow'
                ),
            ]);
        }

        $meta = $seo->metaFor(
            (string) ($profile->professional_title ?: $profile->display_name),
            $profile->short_bio,
            route('home'),
            'index, follow',
            $profile->avatar?->url(),
            'profile'
        );

        return view('pages.home', [
            'meta' => $meta,
            'profile' => $profile,
            'sections' => $composition['sections'],
        ]);
    }
}
