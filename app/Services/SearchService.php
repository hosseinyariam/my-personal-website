<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;
use App\Models\Note;
use App\Models\Product;
use App\Models\Project;
use App\Support\Persian;
use Illuminate\Support\Collection;

/**
 * سرویس جست‌وجو — دیتابیسی در فاز اول.
 *
 * ── چرا دیتابیس و نه موتور جست‌وجو ──
 * معماری:974-990: فاز اول جست‌وجوی دیتابیسی؛ موتور اختصاصی (Elasticsearch)
 * فقط اگر حجم محتوا توجیه کند. هاست اشتراکی هم اجازه‌ی نصب موتور
 * نمی‌دهد. CMS:430 صریحاً Elasticsearch را خارج از MVP گذاشته است.
 *
 * ── چرا Interface ──
 * معماری:990 می‌گوید `SearchService` باید interface داشته باشد تا
 * پیاده‌سازی قابل تعویض باشد. اینجا کلاس مشخص است ولی مرز عمومی‌اش
 * (متد `search`) پایدار می‌ماند تا تعویض بی‌درد باشد.
 *
 * ── محدودیت آگاهانه ──
 * LIKE روی متن فارسی با collation `utf8mb4_unicode_ci` جست‌وجوی
 * بدون حساسیت به بزرگی/کوچکی می‌دهد ولی **بدون ریشه‌یابی**. یعنی
 * «پروژه» و «پروژه‌ها» به‌عنوان یکسان دیده نمی‌شوند. این برای فاز
 * اول قابل قبول است و در فاز بعد با جدول ایندکس بهبود می‌یابد.
 * ────────────────────────────────
 */
class SearchService
{
    /** سقف نتایج — جلوگیری از Query سنگین روی هاست اشتراکی. */
    private const RESULT_LIMIT = 20;

    /** حداقل طول عبارت جست‌وجو. */
    private const MIN_QUERY_LENGTH = 2;

    /** حداکثر طول عبارت — جلوگیری از Queryهای پرهزینه. */
    private const MAX_QUERY_LENGTH = 100;

    /**
     * جست‌وجو در همه‌ی انواع محتوا.
     *
     * @return array{
     *     query: string,
     *     results: Collection<int, array{type: string, label: string, title: string, excerpt: string, url: string}>,
     *     total: int,
     *     too_short: bool
     * }
     */
    public function search(?string $query): array
    {
        $term = $this->normalize($query);

        if (mb_strlen($term) < self::MIN_QUERY_LENGTH) {
            return [
                'query' => $term,
                'results' => collect(),
                'total' => 0,
                'too_short' => filled($term),
            ];
        }

        $results = collect()
            ->merge($this->searchProjects($term))
            ->merge($this->searchProducts($term))
            ->merge($this->searchNotes($term));

        return [
            'query' => $term,
            'results' => $results->take(self::RESULT_LIMIT)->values(),
            'total' => $results->count(),
            'too_short' => false,
        ];
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function searchProjects(string $term): Collection
    {
        if (! Module::isEnabled('projects')) {
            return collect();
        }

        return Project::query()
            ->listable()
            ->where(function ($q) use ($term): void {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('summary', 'like', "%{$term}%")
                    ->orWhere('problem', 'like', "%{$term}%");
            })
            ->limit(self::RESULT_LIMIT)
            ->get()
            ->map(fn (Project $p): array => [
                'type' => 'project',
                'label' => 'پروژه',
                'title' => (string) $p->title,
                'excerpt' => Persian::excerpt($p->summary, 140),
                'url' => route('projects.show', $p),
            ]);
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function searchProducts(string $term): Collection
    {
        if (! Module::isEnabled('products')) {
            return collect();
        }

        return Product::query()
            ->listable()
            ->where(function ($q) use ($term): void {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('summary', 'like', "%{$term}%")
                    ->orWhere('problem', 'like', "%{$term}%");
            })
            ->limit(self::RESULT_LIMIT)
            ->get()
            ->map(fn (Product $p): array => [
                'type' => 'product',
                'label' => 'محصول',
                'title' => (string) $p->name,
                'excerpt' => Persian::excerpt($p->summary, 140),
                'url' => route('products.show', $p),
            ]);
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    private function searchNotes(string $term): Collection
    {
        if (! Module::isEnabled('notes')) {
            return collect();
        }

        return Note::query()
            ->listable()
            ->where(function ($q) use ($term): void {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%")
                    ->orWhere('body', 'like', "%{$term}%");
            })
            ->limit(self::RESULT_LIMIT)
            ->get()
            ->map(fn (Note $n): array => [
                'type' => 'note',
                'label' => 'یادداشت',
                'title' => (string) $n->title,
                'excerpt' => Persian::excerpt($n->excerpt ?: $n->body, 140),
                'url' => route('notes.show', $n),
            ]);
    }

    /**
     * نرمال‌سازی عبارت جست‌وجو.
     *
     * یکسان‌سازی «ی»/«ک» عربی به فارسی: کاربر ممکن است با صفحه‌کلید
     * عربی تایپ کند و بدون این، نتیجه خالی می‌گیرد.
     */
    private function normalize(?string $query): string
    {
        if (blank($query)) {
            return '';
        }

        $term = trim((string) $query);
        $term = str_replace(['ي', 'ك', 'ة', 'ۀ'], ['ی', 'ک', 'ه', 'ه'], $term);
        $term = Persian::toLatinDigits($term);
        // نیم‌فاصله به فاصله — کاربر معمولاً آن را تایپ نمی‌کند
        $term = str_replace("\u{200C}", ' ', $term);
        $term = preg_replace('/\s+/u', ' ', $term) ?? $term;

        // کاراکترهای wildcard در LIKE باید خنثی شوند، وگرنه `%` کاربر
        // به همه‌چیز منطبق می‌شود.
        $term = str_replace(['%', '_'], ['\%', '\_'], $term);

        return mb_substr($term, 0, self::MAX_QUERY_LENGTH);
    }
}
