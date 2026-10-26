<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * پاک‌سازی کش منقضی و کهنه.
 *
 * ── چرا لازم است ──
 * روی هاست اشتراکی Redis وجود ندارد، پس کش روی جدول `cache` ذخیره
 * می‌شود (مستندات: «معماری نباید به Redis وابسته باشد»). ردیف‌های
 * منقضی تا وقتی کسی آن‌ها را نخواند باقی می‌مانند و جدول را بزرگ
 * می‌کنند. روی هاستی که بکاپ هفتگی می‌گیرد، این یعنی بکاپ‌های
 * بزرگ‌تر و کند‌تر.
 *
 * ── چه چیزی پاک می‌شود ──
 * فقط ردیف‌های **منقضی‌شده**. کش معتبر دست‌نخورده می‌ماند — پاک‌کردن
 * کش سالم، صفحه‌های پرترافیک را بی‌دلیل سرد می‌کند و بار دیتابیس
 * را بالا می‌برد.
 */
class PruneStaleCache extends Command
{
    protected $signature = 'cache:prune-stale
                            {--dry-run : فقط شمارش کن، بدون حذف}';

    protected $description = 'حذف ردیف‌های کش منقضی‌شده';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // فقط اگر driver دیتابیسی باشد این کار معنا دارد.
        // با driver های دیگر (file/array) هیچ ردیف منقضی‌ای در
        // دیتابیس نیست.
        $store = config('cache.default');

        if ($store !== 'database') {
            $this->comment("driver کش «{$store}» است، پاک‌سازی ردیف لازم نیست.");

            return self::SUCCESS;
        }

        $expiredCount = DB::table('cache')
            ->where('expiration', '<', now()->getTimestamp())
            ->count();

        if ($expiredCount === 0) {
            $this->comment('ردیف منقضی‌شده‌ای برای حذف نیست.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->line("[آزمایشی] {$expiredCount} ردیف کش منقضی پیدا شد.");

            return self::SUCCESS;
        }

        $deleted = DB::table('cache')
            ->where('expiration', '<', now()->getTimestamp())
            ->delete();

        $this->info("{$deleted} ردیف کش منقضی حذف شد.");

        return self::SUCCESS;
    }
}
