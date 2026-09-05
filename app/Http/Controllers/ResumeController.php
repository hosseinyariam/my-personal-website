<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SkillCategory;
use App\Models\ResumeCertificate;
use App\Models\ResumeEducation;
use App\Models\ResumeExperience;
use App\Models\ResumeSkill;
use App\Services\Settings;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * رزومه (M4) — نسخه‌ی خوانا از داده‌ی ساخت‌یافته + دانلود PDF آپلودی (D09).
 */
class ResumeController extends Controller
{
    public function index(Settings $settings): View
    {
        $pdf = $settings->resumePdfPath();

        return view('pages.resume', [
            'experiences' => ResumeExperience::query()->active()->ordered()->get(),
            'educations' => ResumeEducation::query()->active()->ordered()->get(),
            'certificates' => ResumeCertificate::query()->active()->ordered()->get(),
            // مهارت‌ها به‌تفکیک دسته — دسته‌ی خالی اصلاً رندر نمی‌شود (US-M4-1)
            'skillGroups' => ResumeSkill::query()->active()->ordered()->get()
                ->groupBy(fn (ResumeSkill $skill) => $skill->category->value)
                ->sortBy(fn ($_, string $key) => array_search($key, array_column(SkillCategory::cases(), 'value'), true)),
            'bioLong' => $settings->get('profile', 'bio_long'),
            'hasResumePdf' => $pdf !== null && Storage::disk('public')->exists($pdf),
        ]);
    }

    /**
     * دانلود امن: مسیر ذخیره‌سازی هرگز در URL افشا نمی‌شود و فایل
     * با نام تمیز تحویل داده می‌شود (US-M4-2).
     */
    public function download(Settings $settings): BinaryFileResponse|Response
    {
        $path = $settings->resumePdfPath();

        if ($path === null || ! Storage::disk('public')->exists($path)) {
            throw new NotFoundHttpException;
        }

        $name = str($settings->displayName())->slug()->value() ?: 'resume';

        return response()->download(
            Storage::disk('public')->path($path),
            "{$name}-resume.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }
}
