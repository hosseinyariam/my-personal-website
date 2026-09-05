<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\Settings;
use Illuminate\Http\Response;

/**
 * خوراک RSS 2.0 معتبر از پست‌های منتشرشده (US-M6-3).
 */
class FeedController extends Controller
{
    public function __invoke(Settings $settings): Response
    {
        $posts = Post::query()->published()->latestFirst()->limit(20)->get();

        return response()
            ->view('feed.rss', [
                'posts' => $posts,
                'siteName' => $settings->displayName(),
                'siteDescription' => $settings->get('seo', 'meta_description')
                    ?: $settings->get('profile', 'bio_short')
                    ?: $settings->displayName(),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
