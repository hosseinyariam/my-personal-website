<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Concerns\HasSeo;
use App\Models\ContactChannel;
use App\Models\Module;
use App\Models\Note;
use App\Models\Product;
use App\Models\Project;
use App\Support\Persian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * سرویس SEO — ساخت متادیتا، sitemap و robots.
 *
 * ── اصل حاکم ──
 * معماری:82-84: «SEO by Architecture». یعنی ساختار URL، عنوان و
 * متادیتا از معماری داده می‌آید، نه از پرکردن دستی هر صفحه.
 *
 * اما اصل مهم‌تر (Strategy:481 و IA:481): داده‌ی ساختاریافته و
 * ادعاها **فقط بر اساس اطلاعات واقعی** منتشر می‌شوند. پس این سرویس
 * هرگز Organization/Person/AggregateRating جعلی نمی‌سازد. تنها
 * چیزی که می‌سازد `WebSite` و `BreadcrumbList` است که با ساختار
 * واقعی صفحه مطابق‌اند.
 */
class SeoService
{
    /**
     * متادیتای کامل یک صفحه.
     *
     * @return array{
     *     title: string,
     *     description: string|null,
     *     canonical: string,
     *     robots: string,
     *     og_title: string,
     *     og_description: string|null,
     *     og_image: string|null,
     *     type: string
     * }
     */
    public function metaFor(
        string $title,
        ?string $description = null,
        ?string $canonical = null,
        string $robots = 'index, follow',
        ?string $ogImage = null,
        string $type = 'website'
    ): array {
        $appName = (string) config('app.name');

        // عنوان صفحه همیشه نام سایت را دارد مگر خودش قبلاً داشته باشد —
        // جلوگیری از تکرار «حسین یاریم | حسین یاریم».
        $fullTitle = str_contains($title, $appName) ? $title : "{$title} | {$appName}";

        return [
            'title' => $fullTitle,
            'description' => $description ? Persian::excerpt($description, 160) : null,
            'canonical' => $canonical ?? url()->current(),
            'robots' => $robots,
            'og_title' => $title,
            'og_description' => $description ? Persian::excerpt($description, 160) : null,
            'og_image' => $ogImage,
            'type' => $type,
        ];
    }

    /**
     * متادیتای یک موجودیت محتوایی با فراداده‌ی SEO اختصاصی‌اش.
     */
    public function metaForEntity(
        Model $entity,
        string $fallbackTitle,
        ?string $fallbackDescription,
        ?string $ogImage = null,
        string $type = 'article'
    ): array {
        $hasSeo = in_array(HasSeo::class, class_uses_recursive($entity), true);

        $title = $hasSeo ? $entity->seoTitle() : $fallbackTitle;
        $description = $hasSeo ? $entity->seoDescription() : $fallbackDescription;
        $canonical = $hasSeo ? $entity->canonicalUrl() : null;

        // محتوای unlisted باید noindex بگیرد حتی اگر منتشر شده باشد.
        $robots = 'index, follow';

        if (method_exists($entity, 'isListable') && ! $entity->isListable()) {
            $robots = 'noindex, follow';
        }

        return $this->metaFor(
            $title ?: $fallbackTitle,
            $description ?? $fallbackDescription,
            $canonical,
            $robots,
            $ogImage,
            $type
        );
    }

    /**
     * داده‌ی ساختاریافته‌ی BreadcrumbList.
     *
     * فقط از مسیر واقعی صفحه ساخته می‌شود — هیچ داده‌ی اختیاری
     * یا فرضی وارد نمی‌شود.
     *
     * @param  array<int, array{label: string, url: string|null}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbSchema(array $items): array
    {
        $elements = [];

        foreach (array_values($items) as $index => $item) {
            $element = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
            ];

            if ($item['url'] !== null) {
                $element['item'] = $item['url'];
            }

            $elements[] = $element;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * داده‌ی ساختاریافته‌ی WebSite.
     *
     * فقط نام و آدرس — بدون ادعای Domain Authority، رتبه یا شهرت.
     */
    public function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => (string) config('app.name'),
            'url' => url('/'),
            'inLanguage' => 'fa-IR',
        ];
    }

    /**
     * فهرست URLهای عمومی برای sitemap.
     *
     * قاعده: فقط محتوای `listable` — یعنی منتشرشده و `public`.
     * `unlisted` عمداً حذف می‌شود چون خودش خواسته در فهرست نباشد.
     *
     * @return array<int, array{url: string, lastmod: string, priority: string}>
     */
    public function sitemapEntries(): array
    {
        $entries = [
            ['url' => route('home'), 'lastmod' => now()->toAtomString(), 'priority' => '1.0'],
        ];

        // صفحات ثابت — فقط اگر ماژولشان روشن باشد، وگرنه به ۴۰۴ می‌روند
        // و در sitemap نباید باشند.
        $staticPages = [
            ['route' => 'biography', 'module' => 'biography', 'priority' => '0.9'],
            ['route' => 'projects.index', 'module' => 'projects', 'priority' => '0.9'],
            ['route' => 'products.index', 'module' => 'products', 'priority' => '0.9'],
            ['route' => 'companies.index', 'module' => 'companies', 'priority' => '0.7'],
            ['route' => 'notes.index', 'module' => 'notes', 'priority' => '0.8'],
            ['route' => 'journey', 'module' => 'timeline', 'priority' => '0.7'],
            ['route' => 'contact.show', 'module' => null, 'priority' => '0.6'],
        ];

        foreach ($staticPages as $page) {
            if ($page['module'] !== null && ! Module::isEnabled($page['module'])) {
                continue;
            }

            $entries[] = [
                'url' => route($page['route']),
                'lastmod' => now()->toAtomString(),
                'priority' => $page['priority'],
            ];
        }

        $entries = array_merge($entries, $this->entityEntries(Project::class, 'projects.show'));
        $entries = array_merge($entries, $this->entityEntries(Product::class, 'products.show'));
        $entries = array_merge($entries, $this->entityEntries(Note::class, 'notes.show'));

        return $entries;
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return array<int, array{url: string, lastmod: string, priority: string}>
     */
    private function entityEntries(string $modelClass, string $routeName): array
    {
        return $modelClass::query()
            ->listable()
            ->select(['id', 'slug', 'updated_at'])
            ->get()
            ->map(fn (Model $model): array => [
                'url' => route($routeName, $model),
                'lastmod' => ($model->updated_at ?? now())->toAtomString(),
                'priority' => '0.7',
            ])
            ->all();
    }

    /**
     * محتوای فایل robots.txt.
     *
     * مسیرهای پنل و پیش‌نمایش مسدود می‌شوند. `Disallow: /admin` هم
     * لایه‌ی اضافی است — کنترل واقعی با middleware و احراز هویت است.
     */
    public function robotsContent(): string
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            '# پنل مدیریت — هرگز نباید ایندکس شود',
            'Disallow: /admin',
            'Disallow: /search',
            '',
            '# پارامترهای جست‌وجو محتوای تکراری می‌سازند',
            'Disallow: /*?q=',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return implode("\n", $lines)."\n";
    }

    /** RSS برای یادداشت‌ها. */
    public function feedItems(int $limit = 20): Collection
    {
        return Note::query()
            ->listable()
            ->with(['seo'])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * کانال‌های عمومی تماس — برای داده‌ی ساختاریافته و فوتر.
     *
     * @return Collection<int, ContactChannel>
     */
    public function publicContactChannels(): Collection
    {
        return Cache::remember(
            'public.contact_channels',
            now()->addHours(6),
            fn (): Collection => ContactChannel::query()
                ->public()
                ->ordered()
                ->get()
        );
    }
}
