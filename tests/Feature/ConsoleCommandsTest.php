<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\Note;
use App\Models\Project;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

/**
 * فرمان‌های زمان‌بند — بررسی رفتار واقعی.
 *
 * ── چرا این تست‌ها لازم‌اند ──
 * فرمان‌های زمان‌بند بی‌صدا کار می‌کنند. اگر خراب باشند، کسی متوجه
 * نمی‌شود تا وقتی که مدیر ببیند محتوای زمان‌بندی‌شده‌اش منتشر نشده
 * یا دیتابیس پر از اسپم شده. این تست‌ها همان سکوت را می‌شکنند.
 */

beforeEach(function (): void {
    seedPublicSite();
    Cache::flush();
});

// ── انتشار زمان‌بندی‌شده ──

it('محتوای زمان‌بندی‌شده‌ای که زمانش رسیده را منتشر می‌کند', function (): void {
    $note = Note::create([
        'title' => 'یادداشت زمان‌بندی‌شده',
        'body' => 'متن کافی برای تست این یادداشت.',
        'status' => 'draft',
        'visibility' => 'public',
        'published_at' => now()->subMinutes(5), // زمانش گذشته
    ]);

    $this->assertDatabaseHas('notes', ['id' => $note->getKey(), 'status' => 'draft']);

    $this->artisan('contents:publish-scheduled')->assertSuccessful();

    expect($note->fresh()->status->value)->toBe('published');
});

it('محتوای زمان‌بندی‌شده‌ی آینده را منتشر نمی‌کند', function (): void {
    $note = Note::create([
        'title' => 'یادداشت فردا',
        'body' => 'متن کافی برای تست این یادداشت.',
        'status' => 'draft',
        'visibility' => 'public',
        'published_at' => now()->addDay(),
    ]);

    $this->artisan('contents:publish-scheduled')->assertSuccessful();

    // زمان نرسیده — نباید منتشر شود حتی با اجرای فرمان
    expect($note->fresh()->status->value)->toBe('draft');
});

it('محتوای بدون تاریخ انتشار را دست نمی‌زند', function (): void {
    $note = Note::create([
        'title' => 'یادداشت بدون تاریخ',
        'body' => 'متن کافی برای تست این یادداشت.',
        'status' => 'draft',
        'visibility' => 'public',
        'published_at' => null,
    ]);

    $this->artisan('contents:publish-scheduled')->assertSuccessful();

    expect($note->fresh()->status->value)->toBe('draft');
});

it('حالت آزمایشی فرمان هیچ تغییری اعمال نمی‌کند', function (): void {
    $note = Note::create([
        'title' => 'یادداشت آزمایشی',
        'body' => 'متن کافی برای تست این یادداشت.',
        'status' => 'draft',
        'visibility' => 'public',
        'published_at' => now()->subMinutes(5),
    ]);

    $this->artisan('contents:publish-scheduled --dry-run')->assertSuccessful();

    expect($note->fresh()->status->value)->toBe('draft');
});

it('فرمان زمان‌بند روی محصول و پروژه خطا نمی‌دهد', function (): void {
    // این دو وضعیت انتشار ندارند (تصمیم D17). فرمان نباید آن‌ها را
    // پردازش کند و نباید ValueError بدهد.
    Project::query()->update(['published_at' => now()->subDay()]);
    \App\Models\Product::query()->update(['published_at' => now()->subDay()]);

    $this->artisan('contents:publish-scheduled')->assertSuccessful();
});

// ── پاک‌سازی اسپم ──

it('اسپم قدیمی را حذف می‌کند ولی پیام واقعی را نگه می‌دارد', function (): void {
    $spam = ContactMessage::create([
        'name' => 'اسپم',
        'email' => 'spam@example.com',
        'message' => 'پیام اسپم قدیمی',
        'is_spam' => true,
    ]);

    $real = ContactMessage::create([
        'name' => 'کاربر واقعی',
        'email' => 'real@example.com',
        'message' => 'پیام واقعی',
        'is_spam' => false,
    ]);

    // `created_at` را مستقیم می‌نویسیم چون Eloquent مهر زمانی خودش را
    // در create می‌گذارد و مقدار ورودی را نادیده می‌گیرد.
    ContactMessage::query()->whereKey($spam->getKey())
        ->update(['created_at' => now()->subDays(60)]);
    ContactMessage::query()->whereKey($real->getKey())
        ->update(['created_at' => now()->subDays(60)]);

    $this->artisan('contact:prune-spam')->assertSuccessful();

    expect(ContactMessage::query()->whereKey($spam->getKey())->exists())->toBeFalse()
        ->and(ContactMessage::query()->whereKey($real->getKey())->exists())->toBeTrue();
});

it('اسپم تازه را حذف نمی‌کند', function (): void {
    $recent = ContactMessage::create([
        'name' => 'اسپم تازه',
        'email' => 'new@example.com',
        'message' => 'اسپم تازه',
        'is_spam' => true,
    ]);

    ContactMessage::query()->whereKey($recent->getKey())
        ->update(['created_at' => now()->subDays(2)]);

    $this->artisan('contact:prune-spam')->assertSuccessful();

    // الگوی اسپم تازه باید برای بررسی بماند
    expect(ContactMessage::query()->whereKey($recent->getKey())->exists())->toBeTrue();
});

it('حالت آزمایشی پاک‌سازی اسپم چیزی حذف نمی‌کند', function (): void {
    $spam = ContactMessage::create([
        'name' => 'اسپم',
        'email' => 'spam@example.com',
        'message' => 'اسپم',
        'is_spam' => true,
    ]);

    ContactMessage::query()->whereKey($spam->getKey())
        ->update(['created_at' => now()->subDays(60)]);

    $this->artisan('contact:prune-spam --dry-run')->assertSuccessful();

    expect(ContactMessage::query()->whereKey($spam->getKey())->exists())->toBeTrue();
});

// ── پاک‌سازی کش ──

it('پاک‌سازی کش بدون خطا اجرا می‌شود', function (): void {
    $this->artisan('cache:prune-stale')->assertSuccessful();
});

// ── سلامت سایت ──

it('فرمان سلامت سایت اجرا می‌شود و همه‌ی بخش‌ها را می‌سنجد', function (): void {
    $this->artisan('site:health')
        ->expectsOutputToContain('دیتابیس')
        ->expectsOutputToContain('مسیرها')
        ->assertSuccessful();
});

it('فرمان سلامت خروجی JSON معتبر می‌دهد', function (): void {
    // خروجی را با `Artisan::call` مستقیم می‌گیریم چون در حالت تست،
    // `$this->artisan()` خروجی را می‌بلعد و به `Artisan::output()`
    // نمی‌رسد. برای بررسی قالب JSON، فراخوانی مستقیم درست‌تر است.
    $exitCode = Artisan::call('site:health --json');
    $output = Artisan::output();

    $decoded = json_decode(trim($output), true);

    expect($decoded)->toBeArray()
        ->and($decoded)->not->toBeEmpty()
        ->and($exitCode)->toBeInt();

    // هر ردیف باید کلیدهای موردانتظار را داشته باشد
    expect($decoded[0])->toHaveKeys(['level', 'title', 'detail', 'ok']);
});

it('فرمان سلامت نبود پروفایل را هشدار می‌دهد نه خطای بحرانی', function (): void {
    \App\Models\Profile::query()->delete();

    // صفحه‌ی راه‌اندازی نمایش داده می‌شود، پس این بحرانی نیست
    $this->artisan('site:health')
        ->expectsOutputToContain('پروفایل')
        ->assertSuccessful();
});

// ── ساخت کاربر مدیر ──

it('فرمان ساخت کاربر مدیر رمز کوتاه را رد می‌کند', function (): void {
    $this->artisan('admin:create-user', [
        '--email' => 'new@example.com',
        '--name' => 'کاربر جدید',
    ])
        ->expectsQuestion('رمز عبور (هنگام تایپ نمایش داده نمی‌شود)', 'short')
        ->assertFailed();

    expect(\App\Models\User::query()->where('email', 'new@example.com')->exists())->toBeFalse();
});

it('فرمان ساخت کاربر مدیر ایمیل نامعتبر را رد می‌کند', function (): void {
    $this->artisan('admin:create-user', [
        '--email' => 'not-an-email',
        '--name' => 'کاربر',
    ])->assertFailed();
});
