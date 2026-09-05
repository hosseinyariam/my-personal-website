<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Services\ContentReadiness;
use Filament\Widgets\Widget;

/**
 * ویجت «آمادگی محتوا» — دروازه‌ی تصمیم لانچ نرم (فاز ۳ · پیوست ب).
 *
 * هر ردیف مستقیماً به منبعِ ورودِ همان داده لینک می‌دهد تا مدیر محتوا
 * از داشبورد تا فرمِ درست، یک کلیک فاصله داشته باشد (V1).
 */
class ContentReadinessWidget extends Widget
{
    protected static string $view = 'filament.widgets.content-readiness';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -10;

    public function getViewData(): array
    {
        /** @var ContentReadiness $readiness */
        $readiness = app(ContentReadiness::class);

        return [
            'checks' => $this->withLinks($readiness->checks()),
            'isLaunchReady' => $readiness->isLaunchReady(),
            'pendingCount' => $readiness->pendingCount(),
        ];
    }

    /** مقصد ویرایشِ هر سنجه؛ اگر منبعی هنوز ثبت نشده، لینک حذف می‌شود. */
    private function withLinks(array $checks): array
    {
        $routes = [
            'platforms' => 'filament.admin.resources.platforms.index',
            'profile' => 'filament.admin.pages.settings',
            'social' => 'filament.admin.resources.social-links.index',
            'resume' => 'filament.admin.resources.resume-experiences.index',
            'resume_pdf' => 'filament.admin.pages.settings',
            'notification_email' => 'filament.admin.pages.settings',
            'posts' => 'filament.admin.resources.posts.index',
        ];

        return collect($checks)
            ->map(function (array $check) use ($routes) {
                $name = $routes[$check['key']] ?? null;
                $check['url'] = ($name && \Illuminate\Support\Facades\Route::has($name)) ? route($name) : null;

                return $check;
            })
            ->all();
    }
}
