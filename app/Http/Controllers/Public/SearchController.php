<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * جست‌وجوی داخلی.
 *
 * ── چرا noindex ──
 * صفحات نتیجه‌ی جست‌وجو محتوای تکراری می‌سازند و هیچ ارزش SEO ندارند.
 * دستورالعمل بخش ۱۲ صریحاً «جلوگیری از محتوای تکراری» را الزام کرده.
 * پس هم اینجا `noindex` ست می‌شود و هم در robots.txt مسیر مسدود است.
 */
class SearchController extends Controller
{
    public function __invoke(Request $request, SearchService $search, SeoService $seo): View
    {
        $result = $search->search($request->string('q')->value() ?: null);

        $meta = $seo->metaFor(
            filled($result['query']) ? 'جست‌وجو: '.$result['query'] : 'جست‌وجو',
            'جست‌وجو در پروژه‌ها، محصولات و یادداشت‌ها.',
            route('search'),
            // noindex همیشه — محتوای تکراری و بی‌ارزش SEO
            'noindex, follow'
        );

        return view('pages.search', [
            'meta' => $meta,
            'query' => $result['query'],
            'results' => $result['results'],
            'total' => $result['total'],
            'tooShort' => $result['too_short'],
        ]);
    }
}
