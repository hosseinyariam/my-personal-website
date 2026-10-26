<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Note;
use App\Models\Tag;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * یادداشت‌ها — فهرست، جزئیات، فیلتر دسته و برچسب.
 *
 * ── قاعده‌ی تاریخ ──
 * IA:224 صریح: «تاریخ انتشار، تاریخ ویرایش و زمان مطالعه را فقط زمانی
 * نمایش بده که معنای مشخص و داده معتبر داشته باشند». پس هیچ تاریخ
 * جعلی تولید نمی‌شود — اگر `published_at` نبود، تاریخ ویرایش
 * استفاده می‌شود و اگر آن هم نبود، چیزی نشان داده نمی‌شود.
 *
 * ── چرا فیلتر دسته/برچسب با query param ──
 * مسیرهای تکی برای هر دسته، انفجار مسیر می‌سازد. جست‌وجوی ساده با
 * پارامتر، از نظر SEO هم مدیریت‌پذیرتر است چون canonical روی مسیر
 * اصلی می‌ماند.
 */
class NoteController extends Controller
{
    public function index(Request $request, SeoService $seo): View
    {
        $query = Note::query()->listable()->ordered()->with(['categories', 'tags', 'seo']);

        $activeCategory = null;
        $activeTag = null;

        if ($request->filled('category')) {
            $activeCategory = Category::query()->where('slug', $request->string('category'))->first();

            if ($activeCategory !== null) {
                $query->whereHas('categories', fn ($q) => $q->whereKey($activeCategory->getKey()));
            }
        }

        if ($request->filled('tag')) {
            $activeTag = Tag::query()->where('slug', $request->string('tag'))->first();

            if ($activeTag !== null) {
                $query->whereHas('tags', fn ($q) => $q->whereKey($activeTag->getKey()));
            }
        }

        $notes = $query->paginate(9)->withQueryString();

        // دسته‌ها و برچسب‌ها فقط اگر یادداشت منتشرشده داشته باشند —
        // وگرنه لینک به صفحه‌ی خالی ساخته می‌شود (UI Kit:88).
        $categories = Category::query()->ordered()->withPublishedNotes()->withCount('notes')->get();
        $tags = Tag::query()->withPublishedNotes()->withCount('notes')->get();

        $title = $activeCategory?->name ?? $activeTag?->name ?? 'یادداشت‌ها';

        $meta = $seo->metaFor(
            $title,
            'یادداشت‌هایی درباره ساختن محصول، حل مسئله، مهندسی نرم‌افزار و درس‌های مسیر.',
            route('notes.index')
        );

        // صفحه‌ی فیلترشده نباید در sitemap و ایندکس بیاید — محتوای
        // تکراری می‌سازد. دستورالعمل بخش ۱۲ صریحاً نهی کرده است.
        if ($activeCategory !== null || $activeTag !== null) {
            $meta['robots'] = 'noindex, follow';
        }

        return view('pages.notes.index', compact('meta', 'notes', 'categories', 'tags', 'activeCategory', 'activeTag'));
    }

    public function show(Note $note, SeoService $seo): View
    {
        if (! $note->isViewable()) {
            abort(404);
        }

        $note->load([
            'categories',
            'tags',
            'projects' => fn ($q) => $q->listable(),
            'products' => fn ($q) => $q->listable(),
            'companies' => fn ($q) => $q->listable(),
            'media',
            'seo.ogImage',
            'author',
        ]);

        $meta = $seo->metaForEntity(
            $note,
            (string) $note->title,
            $note->excerpt,
            $note->coverImage()?->url(),
            'article'
        );

        // یادداشت unlisted نباید ایندکس شود — تصمیم در SeoService
        // گرفته می‌شود، اینجا تکرار نمی‌شود.
        if (! $note->isListable()) {
            $meta['robots'] = 'noindex, follow';
        }

        $breadcrumb = $seo->breadcrumbSchema([
            ['label' => 'خانه', 'url' => route('home')],
            ['label' => 'یادداشت‌ها', 'url' => route('notes.index')],
            ['label' => (string) $note->title, 'url' => null],
        ]);

        // یادداشت‌های مرتبط — حلقه‌ی کشف (IA:341-355). بر اساس
        // اشتراک دسته، نه برچسب تصادفی.
        $relatedNotes = Note::query()
            ->listable()
            ->whereKeyNot($note->getKey())
            ->when($note->categories->isNotEmpty(), function ($q) use ($note): void {
                $q->whereHas('categories', fn ($inner) => $inner->whereIn('categories.id', $note->categories->pluck('id')));
            })
            ->ordered()
            ->limit(3)
            ->get();

        return view('pages.notes.show', compact('meta', 'note', 'breadcrumb', 'relatedNotes'));
    }
}
