<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\SeoService;
use Illuminate\Http\Response;

/**
 * خروجی RSS یادداشت‌ها.
 *
 * ── چرا RSS هنوز مهم است ──
 * IA و PRD هر دو RSS را در دامنه‌ی محصول گذاشته‌اند. برای یک سایت
 * هویتی-فنی، RSS کانال طبیعی انتشار است: مخاطب فنی آن را ترجیح
 * می‌دهد بر عضویت در خبرنامه.
 *
 * ── اعتبار XML ──
 * محتوا escape می‌شود و تاریخ‌ها به فرمت RFC 822 هستند که
 * استاندارد RSS 2.0 است. فید نامعتبر بی‌فایده است.
 */
class FeedController extends Controller
{
    public function __invoke(SeoService $seo): Response
    {
        $profile = Profile::active();
        $items = $seo->feedItems(20);

        return response()
            ->view('feed.rss', [
                'items' => $items,
                'profile' => $profile,
                'updated' => $items->first()?->published_at ?? now(),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
