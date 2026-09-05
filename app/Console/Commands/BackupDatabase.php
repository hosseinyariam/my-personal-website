<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * دامپ هفتگی دیتابیس — RPO یک‌هفته‌ای سند (بخش ۱۱ · فاز ۶).
 *
 * روی هاست اشتراکی worker نداریم؛ این فرمان از cron صدا زده می‌شود:
 *   0 3 * * 5 php artisan db:dump
 */
class BackupDatabase extends Command
{
    protected $signature = 'db:dump
        {--path= : پوشه‌ی مقصد (پیش‌فرض: storage/app/backups)}
        {--keep=8 : چند فایل آخر نگه داشته شود}';

    protected $description = 'گرفتن دامپ فشرده از دیتابیس و پاک‌کردن نسخه‌های قدیمی';

    public function handle(): int
    {
        $connection = config('database.default');

        if ($connection !== 'mysql') {
            $this->error("این فرمان فقط برای MySQL/MariaDB است؛ اتصال فعلی: {$connection}");

            return self::FAILURE;
        }

        $config = config("database.connections.{$connection}");
        $directory = $this->option('path') ?: storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $file = $directory.'/db-'.now()->format('Y-m-d_His').'.sql.gz';

        // رمز از طریق متغیر محیطی به mysqldump می‌رسد، نه آرگومان خط فرمان،
        // تا در فهرست پروسه‌های سرور دیده نشود.
        $process = Process::fromShellCommandline(
            'mysqldump --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USER" '
            .'--single-transaction --quick --default-character-set=utf8mb4 '
            .'"$DB_NAME" | gzip > "$DB_OUT"',
            env: [
                'MYSQL_PWD' => (string) $config['password'],
                'DB_HOST' => (string) $config['host'],
                'DB_PORT' => (string) $config['port'],
                'DB_USER' => (string) $config['username'],
                'DB_NAME' => (string) $config['database'],
                'DB_OUT' => $file,
            ],
            timeout: 600,
        );

        $process->run();

        if (! $process->isSuccessful() || ! File::exists($file) || File::size($file) === 0) {
            File::delete($file);
            $this->error('دامپ ناموفق بود: '.trim($process->getErrorOutput()));

            return self::FAILURE;
        }

        $this->info(sprintf('دامپ ساخته شد: %s (%s کیلوبایت)', $file, number_format(File::size($file) / 1024, 1)));

        $this->pruneOldBackups($directory);

        return self::SUCCESS;
    }

    /** فقط N فایل آخر می‌ماند تا فضای هاست اشتراکی پر نشود. */
    private function pruneOldBackups(string $directory): void
    {
        $keep = max(1, (int) $this->option('keep'));

        $stale = collect(File::glob($directory.'/db-*.sql.gz'))
            ->sortByDesc(fn (string $path) => File::lastModified($path))
            ->slice($keep);

        $stale->each(fn (string $path) => File::delete($path));

        if ($stale->isNotEmpty()) {
            $this->line("{$stale->count()} بکاپ قدیمی حذف شد.");
        }
    }
}
