<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContactMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * پاک‌سازی پیام‌های اسپم قدیمی.
 *
 * ── چرا لازم است ──
 * پیام‌های اسپم علامت‌گذاری می‌شوند تا الگویشان قابل بررسی بماند،
 * ولی نگه‌داشتن‌شان برای همیشه بی‌فایده است: جای دیتابیس و بکاپ را
 * می‌گیرد و در هاست اشتراکی با سقف ۵۱۲۰ مگابایت و بکاپ هفتگی،
 * هر مگابایت اهمیت دارد.
 *
 * ── چه چیزی حفظ می‌شود ──
 * فقط اسپم‌های قدیمی حذف می‌شوند. پیام‌های واقعی (حتی خوانده‌نشده)
 * هرگز خودکار حذف نمی‌شوند — حذف مکالمه‌ی واقعی، از دست دادن یک
 * فرصت همکاری است.
 */
class PruneSpamMessages extends Command
{
    protected $signature = 'contact:prune-spam
                            {--days=30 : پیام‌های اسپم قدیمی‌تر از این تعداد روز}
                            {--dry-run : فقط شمارش کن، بدون حذف}';

    protected $description = 'حذف پیام‌های اسپم قدیمی‌تر از حد مشخص';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $query = ContactMessage::query()
            ->where('is_spam', true)
            ->where('created_at', '<', $cutoff);

        $count = $query->count();

        if ($count === 0) {
            $this->comment('پیام اسپم قدیمی‌ای برای حذف نیست.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->line("[آزمایشی] {$count} پیام اسپم قدیمی‌تر از {$days} روز پیدا شد.");

            return self::SUCCESS;
        }

        // حذف واقعی — اینجا forceDelete لازم نیست چون ContactMessage
        // از SoftDeletes استفاده نمی‌کند.
        $deleted = $query->delete();

        Log::info("پاک‌سازی اسپم: {$deleted} پیام قدیمی‌تر از {$days} روز حذف شد.");
        $this->info("{$deleted} پیام اسپم حذف شد.");

        return self::SUCCESS;
    }
}
