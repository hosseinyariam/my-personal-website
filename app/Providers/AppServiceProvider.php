<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Company;
use App\Models\Note;
use App\Models\Product;
use App\Models\Project;
use App\Models\TimelineEvent;
use App\Observers\PurgeMorphRelationsObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerObservers();
        $this->enforceHttpsInProduction();
    }

    /**
     * Observer پاک‌سازی روی همه‌ی موجودیت‌هایی که رابطه‌ی چندریختی دارند.
     *
     * ثبت در یک محل به‌جای attribute روی مدل: اگر مدل جدیدی اضافه شد،
     * فقط یک خط اینجا لازم است و پیدا کردن همه‌ی مدل‌ها آسان می‌ماند.
     */
    private function registerObservers(): void
    {
        foreach ([
            Project::class,
            Product::class,
            Company::class,
            Note::class,
            TimelineEvent::class,
        ] as $model) {
            $model::observe(PurgeMorphRelationsObserver::class);
        }
    }

    /**
     * در محیط تولید همه‌ی URLها HTTPS می‌شوند.
     *
     * چرا لازم است: روی هاست اشتراکی، Apache پشت SSL ترمینیشن است و
     * لاراول ممکن است درخواست را HTTP ببیند. بدون این، لینک‌های
     * تولیدشده `http://` می‌شوند و مرورگر هشدار «محتوای ناامن» می‌دهد.
     */
    private function enforceHttpsInProduction(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
