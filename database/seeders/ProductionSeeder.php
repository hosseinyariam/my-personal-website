<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * ProductionSeeder — تنها seederی که اجرای آن روی هاست زنده بی‌خطر است.
 *
 * ── چرا این تفکیک وجود دارد ──
 * سایت عمومی هرگز نباید محتوای ساختگی نشان دهد. قاعده‌ی سخت برند و
 * معیار پذیرش (DoD #4): «هیچ محتوای ساختگی در خروجی عمومی نباشد».
 * پس این seeder فقط چیزهای «واقعی و ساختاری» را می‌سازد:
 * ماژول‌ها، بخش‌های صفحه اصلی، تنظیمات، دسته‌ها — و مدیر از env.
 *
 * ⚠ هیچ محتوای دمویی (پروژه، محصول، یادداشت، شرکت، پروفایل جعلی)
 * اینجا ساخته نمی‌شود. تمام محتوای دمو فقط در `DemoSeeder` است که
 * روی محیط production اصلاً اجرا نمی‌شود. اگر دنبال داده‌ی نمونه
 * برای توسعه‌ی محلی یا اسکرین‌شات هستید، `DemoSeeder` را ببینید.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ModuleSeeder::class,
            HomepageSectionSeeder::class,
            SettingSeeder::class,
            CategorySeeder::class,
        ]);

        $this->seedFirstAdmin();

        $this->command?->info('ProductionSeeder: ماژول‌ها، بخش‌های صفحه اصلی، تنظیمات و دسته‌ها آماده شد.');
        $this->command?->info('ProductionSeeder: هیچ محتوای عمومی/دمویی ساخته نشد (طبق قاعده‌ی «بدون محتوای ساختگی»).');
    }

    /**
     * ساخت اولین کاربر مدیر، فقط از متغیرهای محیطی.
     *
     * ── چرا رمز هرگز پیش‌فرض یا اختراعی نیست ──
     * Blueprint:331-333: هیچ رمز ثابتی در کد وجود ندارد. اگر
     * ADMIN_PASSWORD خالی باشد، ما رمز نمی‌سازیم — چون رمز ساختگی
     * یعنی یا حدس‌زدنی است (خطر امنیتی) یا جایی چاپ می‌شود (افشا).
     * در این حالت فقط هشدار می‌دهیم و رد می‌شویم.
     *
     * ── چرا رمز کاربر موجود بازنویسی نمی‌شود ──
     * اگر مدیر قبلاً ساخته شده و رمزش را عوض کرده باشد، اجرای دوباره‌ی
     * seeder نباید رمز او را به مقدار env برگرداند. این همان قاعده‌ی
     * «seed هرگز محتوای ویرایش‌شده‌ی مدیر را بازنویسی نمی‌کند» است.
     * پس کاربر موجود دست‌نخورده می‌ماند و فقط گزارش می‌شود.
     */
    private function seedFirstAdmin(): void
    {
        $email = trim((string) config('app.admin_emails', ''));
        $name = trim((string) env('ADMIN_NAME', ''));
        $password = (string) env('ADMIN_PASSWORD', '');

        // فهرست می‌تواند چند ایمیل با کاما داشته باشد؛ برای ساخت کاربر
        // اولین ایمیل معتبر کافی است (بقیه صرفاً مجاز به ورود می‌شوند).
        $email = trim(explode(',', $email)[0] ?? '');

        if ($email === '' || $password === '') {
            $this->command?->warn(
                'ProductionSeeder: ADMIN_EMAIL یا ADMIN_PASSWORD خالی است — کاربر مدیر ساخته نشد. '
                .'رمز پیش‌فرض ساخته نمی‌شود (الزام امنیتی). برای ساخت مدیر، این دو متغیر را ست و دوباره اجرا کنید.'
            );

            return;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->command?->info(
                "ProductionSeeder: کاربر مدیر «{$email}» از قبل وجود دارد — رمز بازنویسی نشد (تصمیم مدیر حفظ می‌شود)."
            );

            return;
        }

        User::query()->create([
            'name' => $name !== '' ? $name : $email,
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);

        $this->command?->info("ProductionSeeder: کاربر مدیر «{$email}» ساخته شد.");
    }
}
