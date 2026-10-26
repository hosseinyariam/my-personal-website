<?php

declare(strict_types=1);

use App\Http\Controllers\Public\BiographyController;
use App\Http\Controllers\Public\CompanyController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\FeedController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\JourneyController;
use App\Http\Controllers\Public\NoteController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\Public\ProjectController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\SeoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| مسیرهای عمومی — جدول قطعی فاز ۲
|--------------------------------------------------------------------------
|
| هر مسیر متعلق به یک ماژول، با middleware `module:` محافظت می‌شود.
| ماژول خاموش = ۴۰۴ صریح. تصمیم آگاهانه: ۴۰۴ نه ۴۰۳ و نه redirect
| (CMS:220-223 سیاست صریح را الزام کرده). دلایل در EnsureModuleEnabled.
|
| ترتیب مهم است: مسیرهای ثابت (`/notes`) باید پیش از مسیرهای پارامتری
| (`/notes/{note}`) تعریف شوند تا تفسیر نادرست رخ ندهد.
|
*/

// ── صفحه اصلی — همیشه فعال، هویت خودِ سایت است ──
Route::get('/', HomeController::class)->name('home');

// ── بیوگرافی ──
Route::middleware('module:biography')->group(function (): void {
    Route::get('/biography', BiographyController::class)->name('biography');
    Route::get('/biography/resume', [BiographyController::class, 'resume'])->name('biography.resume');
});

// ── پروژه‌ها ──
Route::middleware('module:projects')->group(function (): void {
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');
});

// ── محصولات ──
Route::middleware('module:products')->group(function (): void {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
});

// ── شرکت‌ها ──
Route::middleware('module:companies')->group(function (): void {
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/{company:slug}', [CompanyController::class, 'show'])->name('companies.show');
});

// ── یادداشت‌ها ──
Route::middleware('module:notes')->group(function (): void {
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/{note:slug}', [NoteController::class, 'show'])->name('notes.show');
    Route::get('/feed', FeedController::class)->name('feed');
});

// ── مسیر حرفه‌ای ──
Route::middleware('module:timeline')->group(function (): void {
    Route::get('/journey', JourneyController::class)->name('journey');
});

// ── جست‌وجو ──
// throttle: جست‌وجو روی دیتابیس با LIKE می‌تواند سنگین باشد؛
// ۳۰ درخواست در دقیقه برای استفاده‌ی واقعی سخاوتمندانه است.
Route::get('/search', SearchController::class)
    ->middleware('throttle:30,1')
    ->name('search');

// ── تماس ──
// POST با throttle اختصاصی: ۵ ارسال در ساعت به‌ازای IP.
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact-submissions')
    ->name('contact.store');

// ── SEO ──
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
