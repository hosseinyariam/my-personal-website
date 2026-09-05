<?php

declare(strict_types=1);

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DevKitController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| مسیرهای عمومی — جدول ۹.۳ سند PRD
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/platforms', [PlatformController::class, 'index'])->name('platforms.index');
Route::get('/platforms/{platform:slug}', [PlatformController::class, 'show'])->name('platforms.show');

Route::get('/resume', [ResumeController::class, 'index'])->name('resume.index');
Route::get('/resume/download', [ResumeController::class, 'download'])->name('resume.download');

Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')      // ۵ ارسال در ساعت به‌ازای IP (US-M5-2)
    ->name('contact.store');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/feed', FeedController::class)->name('feed');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| صفحه‌ی حسابرسی UI Kit — فقط در محیط محلی (DoD فاز ۱)
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    Route::get('/dev/kit', DevKitController::class)->name('dev.kit');
}
