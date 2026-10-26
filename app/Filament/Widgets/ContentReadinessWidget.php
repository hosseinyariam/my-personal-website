<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\ModuleResource;
use App\Filament\Resources\NoteResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\ProfileResource;
use App\Filament\Resources\ProjectResource;
use App\Models\Module;
use App\Models\Note;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Project;
use App\Support\Persian;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * ویجت «آمادگی محتوا» برای داشبورد.
 *
 * ── چرا فقط یک ویجت و بدون نمودار ──
 * Blueprint:99 صریح است: «داشبورد در MVP نباید به نمودارهای تزئینی یا
 * اعداد بی‌استفاده تبدیل شود. هر کارت باید به یک اقدام واقعی منتهی شود.»
 * به همین دلیل هیچ نمودار، sparkline، آمار ساختگی یا متریک تزئینی
 * اینجا نیست. هر Stat به فهرست همان منبع لینک می‌شود تا مدیر با یک
 * کلیک به اقدام برسد.
 *
 * ── معنای «منتشرشده» ──
 * برای Project/Product ستون `status` چرخه‌ی **دامنه** است نه انتشار
 * (تصمیم D17). پس «منتشرشده» با scope `published()` سنجیده می‌شود که
 * `published_at` و `visibility` را با هم در نظر می‌گیرد. این همان
 * منبع حقیقتی است که صفحات عمومی استفاده می‌کنند — نه یک شمارش دستی
 * که ممکن است با سایت اختلاف پیدا کند.
 */
class ContentReadinessWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            $this->projectStat(),
            $this->productStat(),
            $this->noteStat(),
            $this->profileStat(),
            $this->moduleStat(),
        ];
    }

    private function projectStat(): Stat
    {
        $published = Project::query()->published()->count();
        $draft = Project::query()->count() - $published;

        return Stat::make('پروژه‌ها', $this->pair($published, $draft))
            ->description('منتشرشده · پیش‌نویس')
            ->descriptionIcon('heroicon-m-cube')
            ->color($draft > 0 ? 'warning' : 'success')
            ->url(ProjectResource::getUrl('index'));
    }

    private function productStat(): Stat
    {
        $published = Product::query()->published()->count();
        $draft = Product::query()->count() - $published;

        return Stat::make('محصولات', $this->pair($published, $draft))
            ->description('منتشرشده · پیش‌نویس')
            ->descriptionIcon('heroicon-m-shopping-bag')
            ->color($draft > 0 ? 'warning' : 'success')
            ->url(ProductResource::getUrl('index'));
    }

    private function noteStat(): Stat
    {
        $published = Note::query()->published()->count();
        $draft = Note::query()->count() - $published;

        return Stat::make('یادداشت‌ها', $this->pair($published, $draft))
            ->description('منتشرشده · پیش‌نویس')
            ->descriptionIcon('heroicon-m-document-text')
            ->color($draft > 0 ? 'warning' : 'success')
            ->url(NoteResource::getUrl('index'));
    }

    /**
     * وضعیت پروفایل — چون بدون پروفایل فعال، صفحه‌ی اصلی هویت ندارد.
     */
    private function profileStat(): Stat
    {
        $active = Profile::query()->where('is_active', true)->exists();
        $anyProfile = Profile::query()->exists();

        if (! $anyProfile) {
            return Stat::make('پروفایل', 'ثبت نشده')
                ->description('صفحه‌ی اصلی بدون هویت است')
                ->descriptionIcon('heroicon-m-identification')
                ->color('danger')
                ->url(ProfileResource::getUrl('index'));
        }

        return Stat::make('پروفایل', $active ? 'فعال' : 'غیرفعال')
            ->description($active ? 'هویت عمومی آماده است' : 'هیچ پروفایل فعالی وجود ندارد')
            ->descriptionIcon('heroicon-m-identification')
            ->color($active ? 'success' : 'danger')
            ->url(ProfileResource::getUrl('index'));
    }

    /**
     * تعداد ماژول‌های خاموش — هر خاموشی یعنی مسیر عمومی ۴۰۴ می‌شود.
     */
    private function moduleStat(): Stat
    {
        $off = Module::query()->where('is_enabled', false)->count();
        $total = Module::query()->count();

        return Stat::make('ماژول‌های خاموش', Persian::digits($off).' از '.Persian::digits($total))
            ->description($off > 0 ? 'مسیرهای عمومی این ماژول‌ها غیرفعال است' : 'همه‌ی ماژول‌ها فعال‌اند')
            ->descriptionIcon('heroicon-m-puzzle-piece')
            ->color($off > 0 ? 'warning' : 'success')
            ->url(ModuleResource::getUrl('index'));
    }

    /** «۱۲ · ۳» با ارقام فارسی — منتشرشده · پیش‌نویس. */
    private function pair(int $published, int $draft): string
    {
        return Persian::digits($published).' · '.Persian::digits($draft);
    }
}
