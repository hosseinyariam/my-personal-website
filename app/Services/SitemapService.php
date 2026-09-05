<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Platform;
use App\Models\Post;
use Illuminate\Support\Collection;

/**
 * نقشه‌ی سایت: صفحه‌ی اصلی + صفحات ثابت + پلتفرم‌های فعال + پست‌های منتشرشده.
 * صفحه‌ی وبلاگ فقط با وجود پست وارد می‌شود — تا URLای که حالت خالی نشان
 * می‌دهد به موتور جست‌وجو معرفی نشود.
 */
final class SitemapService
{
    /** @return Collection<int, array{loc: string, lastmod: ?string, changefreq: string, priority: string}> */
    public function urls(): Collection
    {
        $urls = collect([
            $this->entry(route('home'), null, 'weekly', '1.0'),
            $this->entry(route('platforms.index'), null, 'weekly', '0.9'),
            $this->entry(route('resume.index'), null, 'monthly', '0.8'),
            $this->entry(route('contact.show'), null, 'yearly', '0.6'),
        ]);

        Platform::query()->active()->ordered()->get()->each(function (Platform $platform) use ($urls) {
            $urls->push($this->entry(
                route('platforms.show', $platform->slug),
                $platform->updated_at?->toAtomString(),
                'monthly',
                $platform->is_featured ? '0.9' : '0.7',
            ));
        });

        $posts = Post::query()->published()->latestFirst()->get();

        if ($posts->isNotEmpty()) {
            $urls->push($this->entry(route('blog.index'), $posts->first()->published_at?->toAtomString(), 'weekly', '0.8'));

            $posts->each(function (Post $post) use ($urls) {
                $urls->push($this->entry(
                    route('blog.show', $post->slug),
                    $post->updated_at?->toAtomString(),
                    'monthly',
                    '0.7',
                ));
            });
        }

        return $urls;
    }

    /** @return array{loc: string, lastmod: ?string, changefreq: string, priority: string} */
    private function entry(string $loc, ?string $lastmod, string $changefreq, string $priority): array
    {
        return compact('loc', 'lastmod', 'changefreq', 'priority');
    }
}
