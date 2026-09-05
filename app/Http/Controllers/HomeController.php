<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Platform;
use App\Models\Post;
use App\Services\Settings;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * صفحه‌ی اصلی هویتی (M1) — پاسخ به «این کیست، چه می‌کند، چه مدرکی دارد؟»
 */
class HomeController extends Controller
{
    public function __invoke(Settings $settings): View
    {
        $platforms = Cache::rememberForever(
            CacheKeys::PLATFORMS_ACTIVE,
            fn () => Platform::query()->active()->ordered()->get(),
        );

        // کارت تیزر: پلتفرم شاخص، وگرنه اولین پلتفرم فعال
        $featured = $platforms->firstWhere('is_featured', true) ?? $platforms->first();

        // تیزر آخرین یادداشت — فقط با وجود پست منتشرشده (US-M1 / D08)
        $latestPost = Cache::rememberForever(
            CacheKeys::POSTS_LATEST,
            fn () => Post::query()->published()->latestFirst()->first(),
        );

        $resumePdf = $settings->resumePdfPath();

        return view('pages.home', [
            'featuredPlatform' => $featured,
            'activePlatformCount' => $platforms->count(),
            'latestPost' => $latestPost,
            'expertise' => collect($settings->get('profile', 'expertise', []))->filter()->values(),
            'bioLong' => $settings->get('profile', 'bio_long'),
            'isAvailable' => (bool) $settings->get('profile', 'is_available', false),
            // دکمه‌ی رزومه فقط وقتی فایل واقعاً روی دیسک هست (US-M1-3)
            'hasResumePdf' => $resumePdf !== null && Storage::disk('public')->exists($resumePdf),
        ]);
    }
}
