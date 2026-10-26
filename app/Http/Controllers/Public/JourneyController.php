<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TimelineEvent;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

/**
 * مسیر حرفه‌ای — خط زمان انتخاب‌شده.
 *
 * ── اصل «Curated» ──
 * IA:234 صریح: «Timeline باید Curated باشد: سیستم می‌تواند رویدادهای
 * زیادی ذخیره کند، اما فقط نقاط مهم انتخاب‌شده Public شوند».
 *
 * پیاده‌سازی: `scopeHighlights` فقط رویدادهای `is_highlight` را
 * می‌آورد. مدیر می‌تواند ده‌ها رویداد ثبت کند و فقط پنج نقطه‌ی عطف
 * را روی سایت بگذارد. اینطور خط زمان، روایت می‌ماند نه لاگ سیستم.
 *
 * ── گروه‌بندی بر اساس سال ──
 * IA:238-242 نمونه‌ای می‌دهد که سال‌محور است. گروه‌بندی بصری بر
 * اساس سال انجام می‌شود تا مسیر قابل اسکن باشد.
 */
class JourneyController extends Controller
{
    public function __invoke(SeoService $seo): View
    {
        $events = TimelineEvent::query()
            ->highlights()
            ->ordered()
            ->with('timelineable')
            ->get();

        // گروه‌بندی بر اساس سال. رویداد بدون تاریخ در گروه
        // «بدون تاریخ» می‌رود — نه در سال جعلی.
        $eventsByYear = $events
            ->groupBy(fn (TimelineEvent $event): string => $event->year() !== null
                ? (string) $event->year()
                : 'undated')
            ->sortKeysDesc();

        $meta = $seo->metaFor(
            'مسیر حرفه‌ای',
            'نقاط عطف مسیر کاری: از طراحی وب و فریلنسری تا ساخت محصول و کارآفرینی فناوری.',
            route('journey')
        );

        // اگر هیچ رویدادی نیست، صفحه‌ی خالی نمایش داده نمی‌شود بلکه
        // حالت خالی محترمانه — UI Kit:500-501.
        return view('pages.journey', compact('meta', 'eventsByYear', 'events'));
    }
}
