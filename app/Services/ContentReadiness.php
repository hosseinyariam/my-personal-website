<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Platform;
use App\Models\Post;
use App\Models\ResumeCertificate;
use App\Models\ResumeEducation;
use App\Models\ResumeExperience;
use App\Models\ResumeSkill;
use App\Models\SocialLink;
use Illuminate\Support\Facades\Storage;

/**
 * ویجت «آمادگی محتوا» — دروازه‌ی تصمیم لانچ نرم (فاز ۳ و پیوست ب).
 *
 * هر ردیف می‌گوید: چه چیزی لازم است، الان چقدر داریم، و آیا برای لانچ کافی است.
 */
final class ContentReadiness
{
    public function __construct(private readonly Settings $settings) {}

    /** @return array<int, array<string, mixed>> */
    public function checks(): array
    {
        $resumeRows = ResumeExperience::query()->active()->count()
            + ResumeEducation::query()->active()->count()
            + ResumeSkill::query()->active()->count()
            + ResumeCertificate::query()->active()->count();

        $resumePdf = $this->settings->resumePdfPath();
        $hasResumePdf = $resumePdf !== null && Storage::disk('public')->exists($resumePdf);

        return [
            [
                'key' => 'platforms',
                'label' => 'پلتفرم فعال',
                'icon' => 'rocket',
                'count' => $count = Platform::query()->active()->count(),
                'ready' => $count >= 1,
                'requirement' => 'حداقل یک پلتفرم فعال',
                'hint' => 'ویترین بدون رکورد فعال، حالت خالی نشان می‌دهد.',
            ],
            [
                'key' => 'profile',
                'label' => 'بیوی کوتاه',
                'icon' => 'user',
                'count' => (int) filled($this->settings->get('profile', 'bio_short')),
                'ready' => filled($this->settings->get('profile', 'bio_short')),
                'requirement' => 'الزامی برای لانچ',
                'hint' => 'اولین چیزی که کارفرما در سه ثانیه می‌خواند.',
            ],
            [
                'key' => 'social',
                'label' => 'لینک ارتباطی',
                'icon' => 'world',
                'count' => $count = SocialLink::query()->active()->count(),
                'ready' => $count >= 1,
                'requirement' => 'حداقل یک کانال',
                'hint' => 'بدون لینک فعال، کارت‌های راه ارتباطی مخفی می‌مانند.',
            ],
            [
                'key' => 'resume',
                'label' => 'ردیف رزومه',
                'icon' => 'file-cv',
                'count' => $resumeRows,
                'ready' => $resumeRows >= 1 || $hasResumePdf,
                'requirement' => 'PDF یا حداقل یک سابقه',
                'hint' => 'بخش‌های خالی رزومه در صفحه‌ی عمومی مخفی می‌شوند.',
            ],
            [
                'key' => 'resume_pdf',
                'label' => 'فایل PDF رزومه',
                'icon' => 'download',
                'count' => (int) $hasResumePdf,
                'ready' => $hasResumePdf,
                'requirement' => 'دکمه‌ی دانلود بدون فایل رندر نمی‌شود',
                'hint' => 'در تنظیمات ← پروفایل آپلود می‌شود.',
            ],
            [
                'key' => 'notification_email',
                'label' => 'ایمیل اعلان',
                'icon' => 'mail',
                'count' => (int) filled($this->settings->notificationEmail()),
                'ready' => filled($this->settings->notificationEmail()),
                'requirement' => 'الزامی در استقرار',
                'hint' => 'بدون آن، پیام‌ها فقط در همین پنل می‌مانند و ایمیلی ارسال نمی‌شود.',
            ],
            [
                'key' => 'posts',
                'label' => 'یادداشت منتشرشده',
                'icon' => 'article',
                'count' => $count = Post::query()->published()->count(),
                'ready' => true,   // برای لانچ الزامی نیست (فاز ۸.۱)
                'requirement' => 'اختیاری — لینک وبلاگ با اولین پست ظاهر می‌شود',
                'hint' => 'تا انتشار اولین پست، وبلاگ در ناوبری دیده نمی‌شود.',
            ],
        ];
    }

    /** آیا همه‌ی الزامات لانچ برآورده شده‌اند؟ */
    public function isLaunchReady(): bool
    {
        return collect($this->checks())->every(fn (array $check) => $check['ready']);
    }

    public function pendingCount(): int
    {
        return collect($this->checks())->reject(fn (array $check) => $check['ready'])->count();
    }
}
