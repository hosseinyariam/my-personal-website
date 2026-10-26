<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;

/**
 * شرکت‌ها — فهرست و جزئیات.
 *
 * ── قاعده‌ی محتوای کافی ──
 * UI Kit:78-79 صفحه‌ی شرکت را «مشروط به محتوای کافی» کرده است.
 * شرکتی که فقط یک نام دارد نباید صفحه‌ی اختصاصی خالی بگیرد — این
 * صفحه‌ی خالی، اعتبار سایت را کم می‌کند. بررسی در `hasEnoughContent`
 * مدل است و در جزئیات اعمال می‌شود.
 *
 * ── Privacy ──
 * CMS:146-147: اطلاعات محرمانه‌ی شرکت نباید در بخش عمومی بیاید.
 * کنترل با `status`/`published_at` انجام می‌شود (scope listable).
 */
class CompanyController extends Controller
{
    public function index(SeoService $seo): View
    {
        $companies = Company::query()
            ->listable()
            ->ordered()
            ->withCount(['products', 'projects'])
            ->paginate(12);

        $meta = $seo->metaFor(
            'شرکت‌ها',
            'شرکت‌ها و تیم‌هایی که در آن‌ها نقش داشته‌ام و محصولاتی که ساخته‌ایم.',
            route('companies.index')
        );

        return view('pages.companies.index', compact('meta', 'companies'));
    }

    public function show(Company $company, SeoService $seo): View
    {
        if (! $company->isViewable()) {
            abort(404);
        }

        $company->load([
            'products' => fn ($q) => $q->listable(),
            'projects' => fn ($q) => $q->listable(),
            'experiences' => fn ($q) => $q->publishedEntries()->ordered(),
            'media',
            'seo.ogImage',
        ]);

        // صفحه‌ی شرکت فقط اگر محتوای واقعی داشته باشد. این با خالی‌بودن
        // فهرست‌ها بررسی می‌شود، نه با شمارش صرف.
        if (! $company->hasEnoughContent()) {
            abort(404);
        }

        $meta = $seo->metaForEntity(
            $company,
            (string) $company->name,
            $company->summary,
            $company->coverImage()?->url(),
            'organization'
        );

        $breadcrumb = $seo->breadcrumbSchema([
            ['label' => 'خانه', 'url' => route('home')],
            ['label' => 'شرکت‌ها', 'url' => route('companies.index')],
            ['label' => (string) $company->name, 'url' => null],
        ]);

        return view('pages.companies.show', compact('meta', 'company', 'breadcrumb'));
    }
}
