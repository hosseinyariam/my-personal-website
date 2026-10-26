<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * ساخت یا به‌روزرسانی کاربر مدیر.
 *
 * ── چرا یک فرمان اختصاصی ──
 * الزام مستندات (Blueprint:331-333 و دستورالعمل §۸): «ایجاد کاربر مدیر
 * اولیه باید از مسیر امن و مستند انجام شود» و «رمز ثابت را در کد
 * نهایی قرار نده». seeder این کار را در زمان نصب انجام می‌دهد، ولی
 * اگر مدیر خواست کاربر دومی بسازد یا رمز را بازنشانی کند، باید مسیر
 * امنی وجود داشته باشد.
 *
 * ── چرا رمز از آرگومان خط فرمان گرفته نمی‌شود ──
 * آرگومان خط فرمان در `history` شل و در list پروسه‌ها دیده می‌شود.
 * پس رمز از stdin پرسیده می‌شود (مخفی) یا از متغیر محیطی
 * `ADMIN_PASSWORD` خوانده می‌شود.
 *
 * ── چرا هشدار می‌دهد ──
 * کاربری که بسازید فقط اگر ایمیلش در `ADMIN_EMAIL` باشد می‌تواند
 * وارد پنل شود. بدون این هشدار، مدیر یک کاربر می‌سازد و بعد
 * نمی‌فهمد چرا نمی‌تواند وارد شود.
 */
class CreateAdminUser extends Command
{
    protected $signature = 'admin:create-user
                            {--email= : ایمیل کاربر (اگر ندهید، پرسیده می‌شود)}
                            {--name= : نام نمایشی}
                            {--env-password : رمز را از ADMIN_PASSWORD بخوان، نه از stdin}';

    protected $description = 'ساخت یا به‌روزرسانی کاربر مدیر با رمز امن';

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('ایمیل کاربر مدیر');
        $name = $this->option('name') ?: $this->ask('نام نمایشی', 'مدیر سایت');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name],
            ['email' => ['required', 'email:rfc', 'max:190'], 'name' => ['required', 'string', 'max:120']]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $email = mb_strtolower(trim((string) $email));

        // رمز: از متغیر محیطی یا پرسش مخفی
        $password = $this->option('env-password')
            ? (string) env('ADMIN_PASSWORD')
            : (string) $this->secret('رمز عبور (هنگام تایپ نمایش داده نمی‌شود)');

        if (mb_strlen($password) < 12) {
            $this->error('رمز باید حداقل ۱۲ کاراکتر باشد. رمز کوتاه، در برابر حمله‌ی جست‌وجوی فراگیر آسیب‌پذیر است.');

            return self::FAILURE;
        }

        $existing = User::query()->where('email', $email)->first();

        if ($existing !== null) {
            // رمز را بازنشانی نمی‌کنیم مگر صریحاً خواسته شود — جلوگیری
            // از قطع دسترسی ناخواسته‌ی مدیر فعلی.
            $resetPassword = $this->confirm(
                "کاربر «{$email}» وجود دارد. رمز عبورش بازنشانی شود؟",
                false
            );

            if ($resetPassword) {
                $existing->forceFill(['password' => $password, 'is_active' => true])->save();
                $this->info("رمز کاربر «{$email}» بازنشانی شد.");
            } else {
                $this->comment("کاربر «{$email}» بدون تغییر ماند.");
            }

            $this->warnAboutAllowlist($email);

            return self::SUCCESS;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->info("کاربر مدیر «{$email}» ساخته شد.");
        $this->warnAboutAllowlist($email);

        return self::SUCCESS;
    }

    /**
     * هشدار درباره‌ی فهرست مجاز — بدون این، مدیر در بن‌بست می‌ماند.
     */
    private function warnAboutAllowlist(string $email): void
    {
        $allowed = array_filter(array_map(
            'trim',
            explode(',', (string) config('app.admin_emails', ''))
        ));

        $isAllowed = in_array(mb_strtolower($email), array_map('mb_strtolower', $allowed), true);

        if ($isAllowed) {
            $this->info('این ایمیل در فهرست مجاز پنل هست — ورود ممکن است.');

            return;
        }

        $this->newLine();
        $this->warn('⚠ توجه: این ایمیل در ADMIN_EMAIL نیست، پس ورود به پنل ممکن نخواهد بود.');
        $this->line('برای اجازه‌ی ورود، مقدار ADMIN_EMAIL را در .env به این اضافه کنید:');
        $this->line("    ADMIN_EMAIL={$email}");
        $this->line('سپس: php artisan config:clear');
    }
}
