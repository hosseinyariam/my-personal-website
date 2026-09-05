<?php

declare(strict_types=1);

use App\Enums\PlatformStatus;
use App\Models\Platform;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\VinoraPlatformSeeder;

/* ── هدرهای امنیتی (NFR · بخش ۱۱) ──────────────────────── */

it('هدرهای امنیتی روی پاسخ‌های عمومی نشسته‌اند', function () {
    $this->get('/')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
});

it('صفحه‌ی ۴۰۴ هم هدرهای امنیتی دارد', function () {
    $this->get('/platforms/nope')
        ->assertNotFound()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

/* ── D05 · رکورد قفل‌شده‌ی وینورا ───────────────────────── */

it('seed وینورا عیناً متن سند را می‌سازد', function () {
    (new VinoraPlatformSeeder)->run();

    $vinora = Platform::query()->where('slug', 'vinora')->sole();

    expect($vinora->title)->toBe('وینورا')
        ->and($vinora->tagline)->toBe('پلتفرم وبِ هوشمند سلامت تاکستان')
        ->and($vinora->status)->toBe(PlatformStatus::Live)
        ->and($vinora->external_url)->toBe('https://vinora.hosseinyariam.ir/')
        ->and($vinora->is_featured)->toBeTrue()
        ->and($vinora->is_active)->toBeTrue()
        ->and($vinora->problem)->toContain('تشخیص دیرهنگام و حدسیِ بیماری‌های رایج انگور')
        ->and($vinora->solution)->toContain('شناسایی بیماری از روی تصویر');
});

it('اجرای دوباره‌ی seeder ویرایش‌های پنل را بازنویسی نمی‌کند', function () {
    (new VinoraPlatformSeeder)->run();

    Platform::query()->where('slug', 'vinora')->update(['title' => 'وینورا (ویرایش‌شده)']);

    (new VinoraPlatformSeeder)->run();

    expect(Platform::query()->where('slug', 'vinora')->sole()->title)->toBe('وینورا (ویرایش‌شده)')
        ->and(Platform::query()->where('slug', 'vinora')->count())->toBe(1);
});

it('seed تنظیمات هیچ متن ساختگی وارد نمی‌کند', function () {
    // V3: حالت خالی = دیتای واقعی، نه محتوای الکی
    (new SettingsSeeder)->run();

    $settings = app(\App\Services\Settings::class);

    expect($settings->get('profile', 'display_name'))->toBe('حسین یاری')
        ->and($settings->get('profile', 'bio_short'))->toBe('')
        ->and($settings->get('contact', 'notification_email'))->toBe('')
        ->and($settings->get('seo', 'meta_description'))->toBe('');
});

/* ── صفحات خطا ─────────────────────────────────────────── */

it('صفحه‌ی ۴۰۴ با استایل کیت و مسیرهای بازگشت رندر می‌شود', function () {
    $this->get('/this-page-does-not-exist')
        ->assertNotFound()
        ->assertSeeText('این صفحه پیدا نشد')
        ->assertSee(route('home'), false)
        ->assertSee(route('contact.show'), false);
});

/* ── صفحه‌ی حسابرسی کیت ────────────────────────────────── */

it('صفحه‌ی کیت فقط در محیط محلی وجود دارد', function () {
    // در محیط testing مسیر اصلاً ثبت نشده است
    expect(\Illuminate\Support\Facades\Route::has('dev.kit'))->toBeFalse();

    $this->get('/dev/kit')->assertNotFound();
});
