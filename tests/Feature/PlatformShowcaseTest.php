<?php

declare(strict_types=1);

use App\Enums\PlatformStatus;
use App\Models\Platform;

/* ── M2 · ویترین پلتفرم‌ها ─────────────────────────────── */

it('فقط پلتفرم‌های فعال را و به ترتیب sort_order نشان می‌دهد', function () {
    // US-M2-1 (Gherkin): فرض داده‌ی فعال موجود است…
    Platform::factory()->create(['title' => 'دومی', 'slug' => 'second', 'sort_order' => 2]);
    Platform::factory()->create(['title' => 'اولی', 'slug' => 'first', 'sort_order' => 1]);
    Platform::factory()->inactive()->create(['title' => 'مخفی', 'slug' => 'hidden']);

    $response = $this->get('/platforms');

    $response->assertOk()
        ->assertSeeText('اولی')
        ->assertSeeText('دومی')
        ->assertDontSeeText('مخفی')
        // ترتیب: «اولی» باید پیش از «دومی» در HTML بیاید
        ->assertSeeTextInOrder(['اولی', 'دومی']);
});

it('نشان وضعیت را روی هر کارت رندر می‌کند', function () {
    Platform::factory()->create(['status' => PlatformStatus::Beta, 'slug' => 'b']);

    $this->get('/platforms')->assertSeeText('بتا');
});

it('بدون رکورد فعال، حالت خالی با CTA تماس نشان می‌دهد', function () {
    // US-M2-3: صفر رکورد فعال = Empty State، نه صفحه‌ی خالی مرده
    $this->get('/platforms')
        ->assertOk()
        ->assertSeeText('هنوز پلتفرمی برای نمایش نیست')
        ->assertSee(route('contact.show'));
});

/* ── M3 · کیس‌استادی ───────────────────────────────────── */

it('صفحه‌ی پلتفرم را با مسئله و راه‌حل رندر می‌کند', function () {
    $platform = Platform::factory()->create([
        'slug' => 'vinora',
        'title' => 'وینورا',
        'problem' => 'مسئله‌ی آزمایشی',
        'solution' => 'راه‌حل آزمایشی',
    ]);

    $this->get("/platforms/{$platform->slug}")
        ->assertOk()
        ->assertSeeText('وینورا')
        ->assertSeeText('مسئله‌ی آزمایشی')
        ->assertSeeText('راه‌حل آزمایشی');
});

it('فیلدهای خالی، سکشن مربوطه را مخفی می‌کنند', function () {
    // US-M3-1: خالی بود هر فیلد = سکشن مخفی
    $platform = Platform::factory()->create([
        'slug' => 'bare',
        'problem' => null,
        'solution' => null,
        'version' => null,
        'license' => null,
        'repo_url' => null,
    ]);

    $response = $this->get("/platforms/{$platform->slug}")->assertOk();

    // نه نوار تب رندر می‌شود، نه دکمه‌ی سورس، نه کارت مشخصات
    $response->assertDontSee('data-tabs', false)
        ->assertDontSee('data-tab="solution"', false)
        ->assertDontSeeText('کد منبع')
        ->assertDontSeeText('لایسنس');
});

it('بدون اسکرین‌شات، ماک مرورگر را به‌جای فضای خالی رندر می‌کند', function () {
    // US-M3-2: fallback قفل‌شده
    $platform = Platform::factory()->create(['slug' => 'nopics', 'title' => 'بی‌تصویر']);

    expect($platform->activeScreenshots)->toBeEmpty();

    $this->get("/platforms/{$platform->slug}")
        ->assertOk()
        ->assertSee('نمای نمونه از رابط', false);
});

it('اسلاگ نامعتبر خطای ۴۰۴ سفارشی می‌دهد', function () {
    // US-M3-3 (Gherkin): Given اسلاگ وجود ندارد → Then ۴۰۴ با استایل کیت
    $this->get('/platforms/does-not-exist')
        ->assertNotFound()
        ->assertSeeText('این صفحه پیدا نشد');
});

it('پلتفرم غیرفعال از بیرون قابل مشاهده نیست', function () {
    $platform = Platform::factory()->inactive()->create(['slug' => 'hidden']);

    $this->get("/platforms/{$platform->slug}")->assertNotFound();
});

it('CTA پایانی هدف service را از پیش انتخاب می‌کند', function () {
    $platform = Platform::factory()->create(['slug' => 'x']);

    $this->get("/platforms/{$platform->slug}")
        ->assertSee(route('contact.show', ['purpose' => 'service']), false);
});
