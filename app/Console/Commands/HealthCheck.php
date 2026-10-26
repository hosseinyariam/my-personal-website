<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ContactChannel;
use App\Models\Module;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/**
 * بررسی سلامت سایت — smoke test پیش و پس از استقرار.
 *
 * ── چرا این فرمان لازم است ──
 * بخش ۱۵ دستورالعمل یک «Smoke Test» در برنامه‌ی انتشار الزام کرده.
 * بررسی دستی صفحات روی هاست اشتراکی کند و خطاپذیر است. این فرمان
 * همان بررسی‌ها را خودکار و تکرارپذیر انجام می‌دهد و می‌تواند در
 * cron هم برای پایش ۷ روزه‌ی پس از لانچ اجرا شود.
 *
 * ── خروجی چیست ──
 * کد خروج غیرصفر اگر مشکل بحرانی باشد، تا قابل استفاده در اسکریپت
 * انتشار و در `&&` زنجیره‌ی فرمان باشد.
 *
 * ── چه چیزی بررسی می‌شود ──
 *   ۱. اتصال دیتابیس
 *   ۲. اجراشدن همه‌ی migrationها
 *   ۳. تنظیمات محیطی حیاتی (APP_KEY، env، debug)
 *   ۴. وجود کاربر مدیر مجاز
 *   ۵. وجود پروفایل فعال
 *   ۶. وضعیت ماژول‌ها
 *   ۷. راه‌اندازی مسیرهای عمومی
 *   ۸. دسترسی نوشتن به storage
 *   ۹. ذخیره‌سازی link
 */
class HealthCheck extends Command
{
    protected $signature = 'site:health {--json : خروجی JSON برای پایش خودکار}';

    protected $description = 'بررسی سلامت سایت — smoke test پیش و پس از استقرار';

    /** @var list<array{level: string, title: string, detail: string, ok: bool}> */
    private array $results = [];

    public function handle(): int
    {
        $critical = 0;

        $critical += $this->checkDatabase();
        $critical += $this->checkMigrations();
        $critical += $this->checkEnvironment();
        $critical += $this->checkAdmin();
        $critical += $this->checkStorage();
        $critical += $this->checkRoutes();

        $this->checkProfile();
        $this->checkModules();
        $this->checkContactChannels();
        $this->checkStorageLink();

        if ($this->option('json')) {
            $this->line(json_encode($this->results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return $critical > 0 ? self::FAILURE : self::SUCCESS;
        }

        $this->renderTable();

        if ($critical > 0) {
            $this->newLine();
            $this->error("{$critical} مشکل بحرانی پیدا شد.");

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('سایت سالم است.');

        return self::SUCCESS;
    }

    /** @return int تعداد مشکل بحرانی */
    private function checkDatabase(): int
    {
        try {
            DB::connection()->getPdo();
            $this->record('ok', 'دیتابیس', 'اتصال برقرار است.', true);

            return 0;
        } catch (\Throwable $e) {
            // پیام خطا ممکن است اطلاعات اتصال (رمز) را شامل شود،
            // پس فقط نوع استثنا گزارش می‌شود.
            $this->record('critical', 'دیتابیس', 'اتصال برقرار نشد: '.$e::class, false);

            return 1;
        }
    }

    private function checkMigrations(): int
    {
        try {
            $pending = $this->pendingMigrationCount();

            if ($pending === null) {
                $this->record('warn', 'Migrationها', 'وضعیت migration قابل خواندن نبود.', true);

                return 0;
            }

            if ($pending > 0) {
                $this->record('critical', 'Migrationها', "{$pending} migration اجرا نشده است.", false);

                return 1;
            }

            $this->record('ok', 'Migrationها', 'همه‌ی migrationها اجرا شده‌اند.', true);

            return 0;
        } catch (\Throwable $e) {
            $this->record('critical', 'Migrationها', 'بررسی شکست خورد: '.$e::class, false);

            return 1;
        }
    }

    private function pendingMigrationCount(): ?int
    {
        $migrator = app('migrator');
        $files = array_map(
            fn (string $path): string => basename($path, '.php'),
            $migrator->getMigrationFiles($migrator->paths())
        );

        if ($migrator->repositoryExists()) {
            $ran = $migrator->getRepository()->getRan();

            return count(array_diff($files, $ran));
        }

        return count($files);
    }

    private function checkEnvironment(): int
    {
        $problems = [];

        if (blank(config('app.key'))) {
            $problems[] = 'APP_KEY تنظیم نشده';
        }

        $env = app()->environment();

        if ($env === 'production' && config('app.debug') === true) {
            // این یک مشکل امنیتی جدی است: stack trace و متغیرهای
            // محیطی به کاربر نمایش داده می‌شود.
            $problems[] = 'APP_DEBUG در محیط production روشن است';
        }

        if ($env === 'production' && ! str_starts_with((string) config('app.url'), 'https://')) {
            $problems[] = 'APP_URL در محیط production روی https نیست';
        }

        if ($problems !== []) {
            $this->record('critical', 'محیط', implode(' · ', $problems), false);

            return 1;
        }

        $this->record('ok', 'محیط', "محیط: {$env}، پیکربندی درست.", true);

        return 0;
    }

    private function checkAdmin(): int
    {
        $allowed = array_filter(array_map(
            'trim',
            explode(',', (string) config('app.admin_emails', ''))
        ));

        if ($allowed === []) {
            $this->record('critical', 'کاربر مدیر', 'ADMIN_EMAIL خالی است — هیچ‌کس نمی‌تواند وارد پنل شود.', false);

            return 1;
        }

        $activeAdmins = User::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (User $u): bool => in_array(
                mb_strtolower((string) $u->email),
                array_map('mb_strtolower', $allowed),
                true
            ));

        if ($activeAdmins->isEmpty()) {
            $this->record('critical', 'کاربر مدیر', 'هیچ کاربر فعالی با ایمیل مجاز وجود ندارد.', false);

            return 1;
        }

        $this->record('ok', 'کاربر مدیر', $activeAdmins->count().' مدیر فعال.', true);

        return 0;
    }

    private function checkStorage(): int
    {
        $paths = [
            storage_path('app'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ];

        $notWritable = [];

        foreach ($paths as $path) {
            if (! is_dir($path) || ! is_writable($path)) {
                $notWritable[] = str_replace(base_path().'/', '', $path);
            }
        }

        if ($notWritable !== []) {
            // این مشکل بحرانی است چون بدون نوشتن در storage،
            // session و کش کار نمی‌کند و سایت خطا می‌دهد.
            $this->record('critical', 'دسترسی نوشتن', 'غیرقابل نوشتن: '.implode(', ', $notWritable), false);

            return 1;
        }

        $this->record('ok', 'دسترسی نوشتن', 'storage قابل نوشتن است.', true);

        return 0;
    }

    private function checkRoutes(): int
    {
        $required = [
            'home', 'biography', 'projects.index', 'products.index',
            'companies.index', 'notes.index', 'journey', 'contact.show',
            'search', 'feed', 'sitemap', 'robots',
        ];

        $missing = array_values(array_filter($required, fn (string $name): bool => ! Route::has($name)));

        if ($missing !== []) {
            $this->record('critical', 'مسیرها', 'مسیرهای گمشده: '.implode(', ', $missing), false);

            return 1;
        }

        $this->record('ok', 'مسیرها', count($required).' مسیر کلیدی ثبت شده.', true);

        return 0;
    }

    private function checkProfile(): void
    {
        $profile = Profile::active();

        if ($profile === null) {
            // بحرانی نیست: صفحه‌ی راه‌اندازی نمایش داده می‌شود، نه خطا.
            // ولی مدیر باید بداند محتوا وارد نشده.
            $this->record('warn', 'پروفایل', 'پروفایل فعالی وجود ندارد — صفحه اصلی صفحه‌ی راه‌اندازی نشان می‌دهد.', true);

            return;
        }

        $this->record('ok', 'پروفایل', 'پروفایل «'.$profile->display_name.'» فعال است.', true);
    }

    private function checkModules(): void
    {
        $total = Module::query()->count();

        if ($total === 0) {
            $this->record('warn', 'ماژول‌ها', 'هیچ ماژولی seed نشده — همه پیش‌فرض روشن در نظر گرفته می‌شوند.', true);

            return;
        }

        $disabled = Module::query()->where('is_enabled', false)->count();
        $this->record('ok', 'ماژول‌ها', "{$total} ماژول، ".($total - $disabled).' فعال.', true);
    }

    private function checkContactChannels(): void
    {
        $count = ContactChannel::query()->public()->count();

        if ($count === 0) {
            $this->record('warn', 'کانال‌های تماس', 'هیچ کانال عمومی فعالی نیست — بخش تماس ناقص است.', true);

            return;
        }

        $this->record('ok', 'کانال‌های تماس', "{$count} کانال عمومی فعال.", true);
    }

    private function checkStorageLink(): void
    {
        $link = public_path('storage');

        if (! file_exists($link)) {
            $this->record('warn', 'link ذخیره‌سازی', 'public/storage ساخته نشده — php artisan storage:link را اجرا کنید.', true);

            return;
        }

        $this->record('ok', 'link ذخیره‌سازی', 'public/storage موجود است.', true);
    }

    private function record(string $level, string $title, string $detail, bool $ok): void
    {
        $this->results[] = compact('level', 'title', 'detail', 'ok');
    }

    private function renderTable(): void
    {
        $rows = array_map(fn (array $r): array => [
            match ($r['level']) {
                'critical' => '✗ بحرانی',
                'warn' => '⚠ هشدار',
                default => '✓ سالم',
            },
            $r['title'],
            $r['detail'],
        ], $this->results);

        $this->table(['وضعیت', 'بخش', 'توضیح'], $rows);
    }
}
