<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SettingType;
use App\Services\SettingService;
use Illuminate\Database\Seeder;

/**
 * seed تنظیمات عمومی سایت — بدون هیچ رازی.
 *
 * ── چرا از `SettingService::set()` استفاده می‌کنیم نه نوشتن خام مدل ──
 * سرویس، نگهبان کلیدهای حساس را اجرا می‌کند (`assertKeyIsSafe`).
 * اگر روزی کسی اشتباهی کلید API را اینجا بگذارد، seeder با استثنا
 * متوقف می‌شود، نه اینکه بی‌صدا راز را در جدول عمومی بنشاند.
 * قاعده‌ی سخت Blueprint:545-548: جدول `settings` هرگز راز نگه نمی‌دارد.
 *
 * ── صداقت محتوایی (قاعده‌ی برند) ──
 * مقادیر پیش‌فرض عمداً بدون آمار، بدون برتری‌نمایی و بدون ادعای
 * بی‌پشتوانه‌اند. مستندات برند هر ادعای عددی/تفضیلی را ممنوع کرده.
 * مدیر می‌تواند بعداً از پنل ویرایش کند؛ seeder فقط یک نقطه‌ی شروع
 * صادقانه می‌دهد.
 *
 * ── idempotency ──
 * `SettingService::set()` روی کلید `key` با updateOrCreate کار می‌کند،
 * پس اجرای دوباره رکورد تکراری نمی‌سازد.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        /** @var SettingService $settings */
        $settings = app(SettingService::class);

        // ── عمومی ──
        $settings->set(
            'site.tagline',
            'مهندس نرم‌افزار و کارآفرین فناوری',
            SettingType::String,
            'general',
            isPublic: true,
        );

        $settings->set(
            'site.description',
            'محلی برای ثبت پروژه‌ها، محصولات و یادداشت‌های مسیر ساخت و یادگیری.',
            SettingType::String,
            'general',
            isPublic: true,
        );

        $settings->set(
            'site.footer_note',
            'ساخته‌شده در مسیر ساخت.',
            SettingType::String,
            'general',
            isPublic: true,
        );

        // ── SEO ──
        $settings->set(
            'seo.default_description',
            'پروژه‌ها، محصولات و یادداشت‌های حسین یاریم در مسیر ساخت محصول و یادگیری.',
            SettingType::String,
            'seo',
            isPublic: true,
        );

        $settings->set(
            'seo.og_default',
            '',
            SettingType::String,
            'seo',
            isPublic: true,
        );

        // `robots` پیش‌فرض عمومی نیست: مقدارش فقط در تولید سند SEO
        // استفاده می‌شود و لازم نیست در نماهای عمومی سریالایز شود.
        $settings->set(
            'seo.robots_default',
            'index, follow',
            SettingType::String,
            'seo',
            isPublic: false,
        );

        // ── تماس ──
        // ایمیل اطلاع‌رسانی پیام تماس. عمومی نیست: نباید در HTML
        // سریالایز شود، حتی اگر آدرس عمومی باشد.
        $settings->set(
            'contact.notify_email',
            (string) config('mail.from.address', ''),
            SettingType::String,
            'contact',
            isPublic: false,
        );
    }
}
