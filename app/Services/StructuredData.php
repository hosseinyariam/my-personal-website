<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Platform;
use App\Models\Post;
use App\Models\SocialLink;
use Illuminate\Support\Facades\Storage;

/**
 * داده‌ی ساخت‌یافته JSON-LD — Person، SoftwareApplication، Article، BreadcrumbList (فاز ۵).
 *
 * همه‌چیز از دیتابیس ساخته می‌شود؛ فیلدهای خالی از خروجی حذف می‌شوند تا
 * schema معتبر بماند و ادعای بی‌مدرک تولید نشود (V3).
 */
final class StructuredData
{
    public function __construct(private readonly Settings $settings) {}

    /** @return array<string, mixed> */
    public function person(): array
    {
        $avatar = $this->settings->get('profile', 'avatar');

        return $this->clean([
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $this->settings->displayName(),
            'url' => url('/'),
            'jobTitle' => $this->settings->get('profile', 'headline'),
            'description' => $this->settings->get('profile', 'bio_short'),
            'image' => filled($avatar) ? Storage::disk('public')->url($avatar) : null,
            'address' => filled($location = $this->settings->get('profile', 'location'))
                ? ['@type' => 'PostalAddress', 'addressLocality' => $location]
                : null,
            'sameAs' => SocialLink::query()
                ->active()
                ->ordered()
                ->get()
                // mailto/tel نشانی پروفایل نیستند و در sameAs جایی ندارند
                ->filter(fn (SocialLink $l) => str_starts_with($l->url, 'http'))
                ->pluck('url')
                ->values()
                ->all(),
        ]);
    }

    /** @return array<string, mixed> */
    public function softwareApplication(Platform $platform): array
    {
        return $this->clean([
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $platform->title,
            'description' => $platform->tagline ?: $platform->problem,
            'url' => $platform->external_url ?: route('platforms.show', $platform->slug),
            'applicationCategory' => 'WebApplication',
            'operatingSystem' => 'Web',
            'softwareVersion' => $platform->version,
            'license' => $platform->license,
            'author' => [
                '@type' => 'Person',
                'name' => $this->settings->displayName(),
                'url' => url('/'),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    public function article(Post $post): array
    {
        return $this->clean([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->meta_description ?: $post->excerpt,
            'image' => $post->coverUrl(),
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'inLanguage' => 'fa-IR',
            'keywords' => $post->tags->pluck('name')->implode(', ') ?: null,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('blog.show', $post->slug)],
            'author' => [
                '@type' => 'Person',
                'name' => $this->settings->displayName(),
                'url' => url('/'),
            ],
        ]);
    }

    /**
     * @param  array<int, array{label: string, url?: string|null}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $i) => array_filter([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['label'],
                'item' => $item['url'] ?? null,
            ]))->all(),
        ];
    }

    /**
     * حذف بازگشتیِ مقادیر خالی — schema بدون کلیدِ تهی معتبرتر است.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function clean(array $data): array
    {
        return collect($data)
            ->map(fn ($value) => is_array($value) ? $this->clean($value) : $value)
            ->reject(fn ($value) => $value === null || $value === '' || $value === [])
            ->all();
    }
}
