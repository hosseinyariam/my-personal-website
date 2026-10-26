<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SeoService;
use Illuminate\Http\Response;

/**
 * خروجی‌های SEO — sitemap، robots و RSS.
 *
 * ── نکته‌ی مهم درباره‌ی sitemap ──
 * sitemap از `SeoService::sitemapEntries()` ساخته می‌شود که فقط
 * محتوای `listable` را می‌آورد. محتوای `unlisted` عمداً حذف است چون
 * خودش خواسته در فهرست نباشد. مسیرهای ماژول خاموش هم حذف می‌شوند —
 * وگرنه sitemap به صفحات ۴۰۴ لینک می‌داد.
 */
class SeoController extends Controller
{
    public function sitemap(SeoService $seo): Response
    {
        $entries = $seo->sitemapEntries();

        return response()
            ->view('feed.sitemap', ['entries' => $entries])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(SeoService $seo): Response
    {
        return response($seo->robotsContent())
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
