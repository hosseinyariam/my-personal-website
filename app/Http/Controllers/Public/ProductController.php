<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

/**
 * محصولات — فهرست و جزئیات.
 *
 * ── قاعده‌ی سخت درباره‌ی وضعیت ──
 * دستورالعمل: «قابلیت برنامه‌ریزی‌شده را به‌عنوان قابلیت آماده معرفی
 * نکن». این در متد `isPreRelease` مدل Product کدگذاری شده: محصول در
 * وضعیت `idea` یا `development` برچسب «در حال توسعه» می‌گیرد و
 * بخش «قابلیت‌های فعلی» برایش با تردید رندر می‌شود.
 *
 * چرا مهم است: ادعای قابلیت‌نشدنی، دقیقاً همان «ادعای بدون عمل» است
 * که BIM:735-737 به‌عنوان دشمن برند تعریف کرده.
 */
class ProductController extends Controller
{
    public function index(SeoService $seo): View
    {
        $products = Product::query()
            ->listable()
            ->ordered()
            ->paginate(9);

        $meta = $seo->metaFor(
            'محصولات',
            'محصولاتی که ساخته و عرضه کرده‌ام، با وضعیت واقعی هر کدام.',
            route('products.index')
        );

        return view('pages.products.index', compact('meta', 'products'));
    }

    public function show(Product $product, SeoService $seo): View
    {
        if (! $product->isViewable()) {
            abort(404);
        }

        $product->load([
            'companies',
            'projects' => fn ($q) => $q->listable(),
            'notes' => fn ($q) => $q->listable(),
            'media',
            'seo.ogImage',
        ]);

        $meta = $seo->metaForEntity(
            $product,
            (string) $product->name,
            $product->summary ?: $product->tagline,
            $product->coverImage()?->url(),
            'product'
        );

        $breadcrumb = $seo->breadcrumbSchema([
            ['label' => 'خانه', 'url' => route('home')],
            ['label' => 'محصولات', 'url' => route('products.index')],
            ['label' => (string) $product->name, 'url' => null],
        ]);

        return view('pages.products.show', compact('meta', 'product', 'breadcrumb'));
    }
}
