<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Models\Platform;
use App\Models\Post;
use App\Models\SocialLink;
use App\Services\Settings;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * داده‌ی مشترک همه‌ی صفحات — هویت، لینک‌ها، متای پیش‌فرض و کلید فعال‌سازی وبلاگ.
 *
 * تصمیم قفل‌شده D08: لینک ناوبری وبلاگ فقط با وجود حداقل یک پست منتشرشده
 * ظاهر می‌شود. شمارش پست‌ها کش می‌شود و با هر ذخیره‌ی پست باطل می‌گردد.
 */
final class SiteComposer
{
    /** @var array<string, mixed>|null داده‌ی مشترک، یک‌بار در هر درخواست */
    private ?array $shared = null;

    public function __construct(private readonly Settings $settings) {}

    public function compose(View $view): void
    {
        $view->with($this->shared ??= $this->build());
    }

    /** @return array<string, mixed> */
    private function build(): array
    {
        $avatar = $this->settings->get('profile', 'avatar');

        return ([
            'siteName' => $this->settings->displayName(),
            'siteHeadline' => $this->settings->get('profile', 'headline'),
            'siteBioShort' => $this->settings->get('profile', 'bio_short'),
            'siteLocation' => $this->settings->get('profile', 'location'),
            'siteAvatar' => filled($avatar) ? Storage::disk('public')->url($avatar) : null,

            'seoTitle' => $this->settings->get('seo', 'meta_title') ?: $this->settings->displayName(),
            'seoDescription' => $this->settings->get('seo', 'meta_description') ?: $this->settings->get('profile', 'bio_short'),
            'seoImage' => $this->settings->get('seo', 'og_image'),

            'socialLinks' => $this->socialLinks(),
            'footerPlatforms' => $this->footerPlatforms(),
            'blogEnabled' => $this->publishedPostCount() > 0,
        ]);
    }

    private function socialLinks()
    {
        return Cache::rememberForever(
            CacheKeys::SOCIAL_LINKS_ACTIVE,
            fn () => SocialLink::query()->active()->ordered()->get(),
        );
    }

    private function footerPlatforms()
    {
        return Cache::rememberForever(
            CacheKeys::PLATFORMS_ACTIVE,
            fn () => Platform::query()->active()->ordered()->get(),
        )->take(5);
    }

    private function publishedPostCount(): int
    {
        return (int) Cache::rememberForever(
            CacheKeys::POSTS_PUBLISHED_COUNT,
            fn () => Post::query()->published()->count(),
        );
    }
}
