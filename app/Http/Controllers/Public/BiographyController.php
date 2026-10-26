<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Technology;
use App\Services\SeoService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * بیوگرافی — «پرونده‌ی حرفه‌ای»، نه رزومه.
 *
 * ── اصل حاکم ──
 * IA:97 صریح: «Biography یک Professional Profile / Professional Case File
 * کامل است، نه Resume ساده». اصل IA:603: «Biography ≠ Resume».
 *
 * تفاوت عملی: رزومه یک فهرست است؛ بیوگرافی یک روایت با شواهد است.
 * به همین دلیل تجربه‌ها، پروژه‌های مرتبط و آموخته‌ها کنار هم آمده‌اند،
 * نه در جدول‌های جدا.
 *
 * ── قاعده‌ی شواهد ──
 * مهارت‌ها فقط اگر به پروژه‌ی عمومی وصل باشند نمایش داده می‌شوند
 * (`scopeEvidenced`). دستاورد بدون شاهد هم فیلتر می‌شود. دلیل:
 * Strategy:505 «Evidence > Skill List» و BIM:161 «ادعای مهارتی که
 * نداری نکن».
 */
class BiographyController extends Controller
{
    public function __invoke(SeoService $seo): View
    {
        $profile = Profile::active();

        if ($profile === null) {
            abort(404);
        }

        $experiences = Experience::query()
            ->publishedEntries()
            ->with('company')
            ->ordered()
            ->get();

        $educations = Education::query()
            ->publishedEntries()
            ->ordered()
            ->get();

        // مهارت‌ها فقط با شاهد واقعی: مهارتی که به هیچ پروژه‌ی عمومی
        // وصل نیست، ادعای بی‌پشتوانه است.
        $skills = Skill::query()
            ->active()
            ->evidenced()
            ->ordered()
            ->get()
            ->groupBy('category');

        $technologies = Technology::query()
            ->active()
            ->evidenced()
            ->ordered()
            ->get();

        // دستاورد بدون شاهد نمایش داده نمی‌شود (scopeEvidenced).
        $achievements = Achievement::query()
            ->publishedEntries()
            ->evidenced()
            ->ordered()
            ->get();

        $certificates = Certificate::query()
            ->publishedEntries()
            ->ordered()
            ->get();

        // پروژه‌های مرتبط — حلقه‌ی کشف از بیوگرافی به کار واقعی.
        $projects = Project::query()
            ->listable()
            ->ordered()
            ->limit(6)
            ->get();

        $companies = Company::query()
            ->listable()
            ->ordered()
            ->get();

        $meta = $seo->metaFor(
            'درباره من',
            $profile->short_bio ?: 'پرونده‌ی حرفه‌ای، تجربه‌ها و مسیر کاری حسین یاریم.',
            route('biography'),
            'index, follow',
            $profile->avatar?->url(),
            'profile'
        );

        return view('pages.biography', compact(
            'meta',
            'profile',
            'experiences',
            'educations',
            'skills',
            'technologies',
            'achievements',
            'certificates',
            'projects',
            'companies'
        ));
    }

    /**
     * دانلود رزومه — فقط اگر فایل واقعی آپلود شده باشد.
     *
     * ── چرا این متد ممکن است ۴۰۴ بدهد ──
     * IA:255 و دستورالعمل: «فایل رزومه نباید از داده‌های ساختگی
     * تولید شود». اگر مدیر فایل PDF واقعی آپلود نکرده باشد، دکمه‌ی
     * دانلود وجود ندارد. این متد هم همان سیاست را در سرور اعمال
     * می‌کند تا کسی با URL مستقیم به فایل ساختگی نرسد.
     */
    public function resume(SeoService $seo): BinaryFileResponse
    {
        $profile = Profile::active();

        if ($profile === null || ! $profile->hasResume()) {
            abort(404);
        }

        $media = $profile->resume;

        // نام فایل دانلودی خوانا و امن ساخته می‌شود — نه مسیر تصادفی
        // ذخیره‌سازی که برای کاربر بی‌معناست.
        $downloadName = 'Hossein-Yariam-Resume.'.pathinfo((string) $media->path, PATHINFO_EXTENSION);

        return response()->download(
            storage_path('app/public/'.$media->path),
            $downloadName,
            ['Content-Type' => (string) $media->mime_type]
        );
    }
}
