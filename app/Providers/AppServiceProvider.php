<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Platform;
use App\Models\PlatformScreenshot;
use App\Models\Post;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Observers\PlatformObserver;
use App\Observers\PlatformScreenshotObserver;
use App\Observers\PostObserver;
use App\Observers\SettingObserver;
use App\Observers\SocialLinkObserver;
use App\Services\Settings;
use App\View\Composers\SiteComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Settings::class);
        $this->app->singleton(SiteComposer::class);
    }

    public function boot(): void
    {
        // ── ابطال کش محتوا از پنل مدیریت ──────────────────
        Platform::observe(PlatformObserver::class);
        PlatformScreenshot::observe(PlatformScreenshotObserver::class);
        Post::observe(PostObserver::class);
        SocialLink::observe(SocialLinkObserver::class);
        Setting::observe(SettingObserver::class);

        // ── داده‌ی مشترک لِی‌اوت ───────────────────────────
        // یک نمونه‌ی singleton که داده را فقط یک‌بار در هر درخواست می‌سازد
        View::composer(['components.layouts.*', 'partials.*', 'pages.*', 'dev.*', 'components.errors.*'], SiteComposer::class);

        // ── صفحه‌بندی با استایل کیت ────────────────────────
        Paginator::defaultView('vendor.pagination.kit');
        Paginator::defaultSimpleView('vendor.pagination.kit');

        // ── HTTPS در پروداکشن (هاست اشتراکی پشت پراکسی) ────
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
