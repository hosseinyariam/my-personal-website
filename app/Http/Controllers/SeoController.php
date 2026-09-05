<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /** نقشه‌ی سایت داینامیک — همیشه هم‌گام با دیتابیس، بدون کار دستی (V4). */
    public function sitemap(SitemapService $sitemap): Response
    {
        return response()
            ->view('feed.sitemap', ['urls' => $sitemap->urls()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /analytics',
            'Disallow: /dev',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ];

        return response(implode("\n", $lines))
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
