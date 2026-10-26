<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * پروژه‌ها — فهرست و جزئیات.
 *
 * ── چرا جزئیات اینقدر کامل است ──
 * IA:129-142 ساختار پرونده‌ی پروژه را مشخص کرده: مسئله، زمینه، نقش،
 * مشارکت، فناوری، نتیجه، آموخته‌ها. این بخش‌ها تزئینی نیستند — هر
 * کدام یک حلقه از زنجیره‌ی «مسئله → ساخت → یادگیری» را مستند می‌کند
 * که هسته‌ی هویت «سازنده قابل اعتماد» است (Strategy:929-941).
 *
 * ── آموخته‌ها حتی در پروژه‌ی ناموفق ──
 * IA:144-152 صریحاً «Failed / Learned» را یک وضعیت معتبر می‌داند.
 * پروژه‌ی متوقف‌شده با درس واقعی، شاهد قوی‌تری از پروژه‌ی موفق
 * بی‌جزئیات است. پس هیچ‌جا پروژه‌ی paused/archived پنهان نمی‌شود.
 */
class ProjectController extends Controller
{
    public function index(SeoService $seo): View
    {
        $projects = Project::query()
            ->listable()
            ->ordered()
            ->paginate(9);

        // حالت خالی یک صفحه‌ی معتبر است، نه خطا. UI Kit:500-501
        // empty-state را الزام کرده — صفحه‌ی سفید ممنوع.
        $meta = $seo->metaFor(
            'پروژه‌ها',
            'پروژه‌هایی که ساخته‌ام: مسئله، رویکرد، راه‌حل و آموخته‌ها.',
            route('projects.index')
        );

        return view('pages.projects.index', compact('meta', 'projects'));
    }

    public function show(Project $project, SeoService $seo): View
    {
        // محافظت در سطح مدل، نه فقط در Query: مسیر با slug کار می‌کند
        // پس باید بررسی شود این پروژه واقعاً قابل نمایش است. بدون این،
        // پیش‌نویس با دانستن slug لو می‌رفت.
        if (! $project->isViewable()) {
            abort(404);
        }

        // لود روابط موردنیاز در یک جا — جلوگیری از N+1 روی هاست اشتراکی
        $project->load([
            'technologies',
            'skills',
            'companies',
            'products' => fn ($q) => $q->listable(),
            'notes' => fn ($q) => $q->listable(),
            'media',
            'seo.ogImage',
        ]);

        $meta = $seo->metaForEntity(
            $project,
            (string) $project->title,
            $project->summary,
            $project->coverImage()?->url(),
            'article'
        );

        // داده‌ی ساختاریافته‌ی مسیر — از ساختار واقعی صفحه ساخته می‌شود.
        $breadcrumb = $seo->breadcrumbSchema([
            ['label' => 'خانه', 'url' => route('home')],
            ['label' => 'پروژه‌ها', 'url' => route('projects.index')],
            ['label' => (string) $project->title, 'url' => null],
        ]);

        return view('pages.projects.show', compact('meta', 'project', 'breadcrumb'));
    }
}
