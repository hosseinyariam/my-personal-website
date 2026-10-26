<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Note;
use App\Models\Product;
use App\Models\Project;
use App\Models\TimelineEvent;
use App\Services\PublishingService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * انتشار محتوایی که زمانش رسیده است.
 *
 * ── چرا این فرمان لازم است ──
 * مدیر می‌تواند `published_at` را در آینده بگذارد تا محتوا در زمان
 * مشخصی منتشر شود. Concern `Publishable` تا رسیدن آن زمان محتوا را
 * نشان نمی‌دهد (fail-closed). ولی برای مدل‌هایی که **وضعیت انتشار**
 * دارند (Note/Company/TimelineEvent)، تا وقتی `status` روی `draft`
 * باشد، فقط رسیدن زمان کافی نیست — باید وضعیت هم عوض شود.
 *
 * این فرمان همان حلقه‌ی گمشده است: روی cron هر دقیقه اجرا می‌شود و
 * محتوای زمان‌بندی‌شده را واقعاً منتشر می‌کند.
 *
 * ── نکته‌ی مهم درباره‌ی Product و Project ──
 * این دو وضعیت انتشار ندارند (تصمیم D17)؛ انتشارشان فقط با
 * `published_at` سنجیده می‌شود. پس آن‌ها نیازی به این فرمان ندارند
 * و صریحاً از فهرست بیرون گذاشته شده‌اند. یک کامنت توضیحی در همان
 * فایل سرویس هم هست.
 * ──
 */
class PublishScheduledContent extends Command
{
    protected $signature = 'contents:publish-scheduled
                            {--dry-run : فقط نمایش بده چه چیزی منتشر می‌شد، بدون تغییر}';

    protected $description = 'انتشار محتوایی که زمان انتشارش رسیده ولی هنوز در وضعیت پیش‌نویس است';

    /**
     * مدل‌هایی که **وضعیت انتشار** دارند.
     *
     * Project و Product عمداً اینجا نیستند — وضعیتشان چرخه‌ی دامنه
     * است و انتشارشان خودکار با رسیدن `published_at` انجام می‌شود.
     *
     * @var list<class-string<Model>>
     */
    private const PUBLISHABLE_MODELS = [
        Note::class,
        Company::class,
        TimelineEvent::class,
    ];

    public function handle(PublishingService $publishing): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $total = 0;

        foreach (self::PUBLISHABLE_MODELS as $modelClass) {
            $due = $publishing->dueForScheduledPublishing($modelClass);

            if ($due->isEmpty()) {
                continue;
            }

            $label = class_basename($modelClass);

            foreach ($due as $model) {
                $title = $model->title ?? $model->name ?? ('#'.$model->getKey());

                if ($dryRun) {
                    $this->line("[آزمایشی] {$label}: {$title}");

                    continue;
                }

                // انتشار از سرویس عبور می‌کند تا `published_at` حفظ شود
                // و کش باطل گردد. تاریخ آینده‌دار اینجا نمی‌آید چون
                // شرط فرمان «زمان رسیده» است.
                $publishing->publish($model);

                $this->info("منتشر شد — {$label}: {$title}");
                $total++;
            }
        }

        if ($dryRun) {
            $this->comment('حالت آزمایشی: هیچ تغییری اعمال نشد.');

            return self::SUCCESS;
        }

        if ($total === 0) {
            // سکوت عمدی: اجرای هر دقیقه روی cron نباید لاگ پر کند.
            // فقط در حالت debug ثبت می‌شود.
            Log::debug('زمان‌بند انتشار: محتوایی برای انتشار نبود.');

            return self::SUCCESS;
        }

        Log::info("زمان‌بند انتشار: {$total} محتوا منتشر شد.");
        $this->info("{$total} محتوا منتشر شد.");

        return self::SUCCESS;
    }
}
