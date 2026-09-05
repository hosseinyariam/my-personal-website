<?php

declare(strict_types=1);

use App\Models\Platform;
use App\Models\Post;
use App\Models\ResumeExperience;
use App\Models\ResumeSkill;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/* ── M1 · صفحه‌ی اصلی ──────────────────────────────────── */

it('هویت و بیو را در صفحه‌ی اصلی نشان می‌دهد', function () {
    // US-M1-1: عنوان و بیو باید بالای صفحه دیده شوند
    $this->setting('profile', 'display_name', 'حسین یاری');
    $this->setting('profile', 'headline', 'توسعه‌دهنده‌ی وب');
    $this->setting('profile', 'bio_short', 'محصول واقعی می‌سازم.');

    $this->get('/')
        ->assertOk()
        ->assertSeeText('حسین یاری')
        ->assertSeeText('توسعه‌دهنده‌ی وب')
        ->assertSeeText('محصول واقعی می‌سازم.');
});

it('کارت تیزر پلتفرم شاخص را با وضعیت و لینک نشان می‌دهد', function () {
    // US-M1-2
    Platform::factory()->featured()->create(['title' => 'وینورا', 'slug' => 'vinora']);

    $this->get('/')
        ->assertOk()
        ->assertSeeText('پلتفرم شاخص')
        ->assertSeeText('وینورا')
        ->assertSee(route('platforms.show', 'vinora'), false);
});

it('تیزر آخرین یادداشت فقط با وجود پست رندر می‌شود', function () {
    $this->get('/')->assertDontSeeText('آخرین یادداشت');

    Post::factory()->create(['title' => 'یادداشت اول']);

    $this->get('/')->assertSeeText('آخرین یادداشت')->assertSeeText('یادداشت اول');
});

it('دکمه‌ی رزومه فقط با وجود فایل PDF رندر می‌شود', function () {
    // US-M1-3 · US-M4-2 · D09
    $this->get('/')->assertDontSee(route('resume.download'), false);

    Storage::fake('public');
    Storage::disk('public')->put('resume/cv.pdf', 'pdf-bytes');
    $this->setting('profile', 'resume_pdf', 'resume/cv.pdf');

    $this->get('/')->assertSee(route('resume.download'), false);
});

/* ── M4 · رزومه ────────────────────────────────────────── */

it('تجربه را در تایم‌لاین و مهارت‌ها را به‌تفکیک دسته نشان می‌دهد', function () {
    ResumeExperience::query()->create([
        'company' => 'وینورا', 'role' => 'توسعه‌دهنده',
        'started_at' => '2023-01-01', 'ended_at' => null,
        'achievements' => "دستاورد اول\nدستاورد دوم",
        'is_active' => true, 'sort_order' => 0,
    ]);
    ResumeSkill::query()->create(['name' => 'Laravel', 'category' => 'technical', 'is_active' => true]);
    ResumeSkill::query()->create(['name' => 'کشف محصول', 'category' => 'product', 'is_active' => true]);

    $this->get('/resume')
        ->assertOk()
        ->assertSeeText('توسعه‌دهنده')
        ->assertSeeText('دستاورد اول')
        ->assertSeeText('دستاورد دوم')
        ->assertSeeText('فنی')
        ->assertSeeText('Laravel')
        ->assertSeeText('محصول')
        ->assertSeeText('کشف محصول');
});

it('رزومه‌ی کاملاً خالی حالت خالی نشان می‌دهد', function () {
    $this->get('/resume')->assertOk()->assertSeeText('رزومه هنوز تکمیل نشده است');
});

it('ردیف غیرفعال رزومه نمایش داده نمی‌شود', function () {
    ResumeSkill::query()->create(['name' => 'مخفی', 'category' => 'technical', 'is_active' => false]);

    $this->get('/resume')->assertDontSeeText('مخفی');
});

it('دانلود رزومه بدون فایل ۴۰۴ می‌دهد', function () {
    $this->get('/resume/download')->assertNotFound();
});

it('دانلود رزومه مسیر ذخیره‌سازی را افشا نمی‌کند', function () {
    // US-M4-2: دانلود امن
    Storage::fake('public');
    Storage::disk('public')->put('resume/secret-path.pdf', '%PDF-1.4 fake');
    $this->setting('profile', 'resume_pdf', 'resume/secret-path.pdf');
    $this->setting('profile', 'display_name', 'حسین یاری');

    $response = $this->get('/resume/download')->assertOk();

    expect($response->headers->get('content-disposition'))
        ->not->toContain('secret-path')
        ->toContain('resume.pdf');
});
