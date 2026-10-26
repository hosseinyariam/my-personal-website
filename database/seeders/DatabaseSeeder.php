<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — نقطه‌ی ورود پیش‌فرض `php artisan db:seed`.
 *
 * ── سیاست امن (پیش‌فرض) ──
 * همیشه `ProductionSeeder` اجرا می‌شود: فقط داده‌ی ساختاری واقعی
 * (ماژول، بخش صفحه اصلی، تنظیمات، دسته) و مدیر از env. این برای
 * هاست زنده بی‌خطر است و هیچ محتوای ساختگی نمی‌سازد.
 *
 * ── داده‌ی دمو فقط در محیط توسعه ──
 * `DemoSeeder` که محتوای نمایشی می‌سازد، **فقط** وقتی اجرا می‌شود که
 * محیط `local` یا `testing` باشد. روی production اصلاً صدا زده
 * نمی‌شود (علاوه بر گارد داخلی خودِ DemoSeeder، اینجا هم فیلتر
 * می‌شود — دو لایه دفاعی).
 *
 * نکته: عمداً از trait `WithoutModelEvents` استفاده نمی‌کنیم. آن trait
 * رویدادهای مدل را خاموش می‌کند و در نتیجه hookهای حیاتی از کار
 * می‌افتند: ساخت خودکار slug در Category و باطل‌کردن کش در
 * Module/HomepageSection. بدون آن‌ها، seed دومین دسته با نقض قید
 * یگانگی شکست می‌خورد و کش کهنه می‌ماند.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // پیش‌فرض امن — روی هر محیطی قابل اجراست.
        $this->call(ProductionSeeder::class);

        // داده‌ی دمو فقط برای توسعه/تست.
        if (app()->environment(['local', 'testing'])) {
            $this->call(DemoSeeder::class);
        }
    }
}
