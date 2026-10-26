<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\AvatarProviders\LocalInitialsAvatarProvider;
use App\Filament\Pages\Auth\Login;
use App\Filament\Widgets\ContentReadinessWidget;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * پنل مدیریت — تنظیمات قطعی پنل.
 *
 * ── راست‌به‌چپ و فارسی ──
 * جهت صفحه از فایل ترجمه‌ی خود Filament (`filament-panels::layout.direction`)
 * خوانده می‌شود. با ست‌کردن locale پنل روی `fa`، همان فایل بارگذاری
 * می‌شود و `dir="rtl"` خودکار اعمال می‌گردد — نیازی به CSS دستی نیست.
 *
 * ── محدودیت نرخ ورود ──
 * صفحه‌ی ورود Filament خودش `rateLimit(5)` را در متد `authenticate()`
 * صدا می‌زند، ولی کلید آن با limiter متمرکز پروژه (`admin-login` در
 * `routes/console.php`) یکی نیست. بررسی شد: Filament در نسخه‌ی 3.3
 * هیچ `throttle` روی مسیر login نمی‌گذارد. برای یک‌منبع-حقیقت شدن
 * محدودیت، صفحه‌ی ورود سفارشی `App\Filament\Pages\Auth\Login` همان
 * limiter نام‌دار را اعمال می‌کند (دفاع در عمق با لایه‌ی Livewire).
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->brandName('پنل مدیریت — حسین یاریم')
            ->colors([
                // رنگ اصلی از دیزاین‌سیستم پروژه (accent) می‌آید، نه رنگ پیش‌فرض Filament
                'primary' => Color::hex('#2563EB'),
                'danger' => Color::hex('#B91C1C'),
                'success' => Color::hex('#15803D'),
                'warning' => Color::hex('#B45309'),
                'info' => Color::hex('#0369A1'),
            ])
            /*
            | ── فونت فارسی برای پنل — بدون هیچ درخواست خارجی ──────
            | Filament به‌طور پیش‌فرض `Inter` را از یک CDN خارجی
            | می‌آورد. دو مشکل:
            |   ۱. متن فارسی با Inter ناقص و ناخوانا رندر می‌شود
            |      (گلیف‌ها و کشیدگی فارسی پشتیبانی نمی‌شود).
            |   ۲. وابستگی به سرویس خارجی — مستندات صریحاً منع کرده‌اند
            |      («وابستگی غیرضروری به سرویس‌های خارجی ایجاد نکن») و
            |      این سرویس‌ها در ایران در دسترس نیستند.
            |
            | نکته‌ی مهم که بررسی شد: `->font('Vazirmatn')` به‌تنهایی
            | کافی نیست. پیش‌فرض `fontProvider` در Filament روی
            | `BunnyFontProvider` است و بدون صراحت، لینک خارجی ساخته
            | می‌شود. با `LocalFontProvider` و `fontUrl = null` هیچ
            | تگ لینکی تولید نمی‌شود — چون `@font-face` وزیرمتن از
            | قبل در `resources/css/app.css` تعریف شده و همان CSS در
            | پنل هم بارگذاری می‌شود.
            */
            ->font(
                family: 'Vazirmatn',
                url: null,
                provider: LocalFontProvider::class,
            )
            // ── گروه‌بندی ناوبری، منطبق بر CMS Blueprint §3 و Baseline §4 ──
            ->navigationGroups([
                NavigationGroup::make()->label('هویت'),
                NavigationGroup::make()->label('نمونه‌کار'),
                NavigationGroup::make()->label('محتوا'),
                NavigationGroup::make()->label('مسیر'),
                NavigationGroup::make()->label('نمایش'),
                NavigationGroup::make()->label('رسانه'),
                NavigationGroup::make()->label('تنظیمات'),
            ])
            /*
            | ── آواتار محلی، نه سرویس خارجی ──────────────────────
            | پیش‌فرض Filament آواتار را از `ui-avatars.com` می‌گیرد.
            | این هم یک وابستگی خارجی است (مستندات منع کرده‌اند و در
            | ایران در دسترس نیست) و هم نام کامل مدیر را به یک سرور
            | ثالث می‌فرستد.
            |
            | `LocalInitialsAvatarProvider` یک Data URI با حرف اول نام
            | می‌سازد: صفر درخواست شبکه و بدون افشای اطلاعات.
            */
            ->defaultAvatarProvider(LocalInitialsAvatarProvider::class)
            /*
            | ── بارگذاری CSS پروژه در پنل ────────────────────────
            | `@font-face` وزیرمتن در `resources/css/app.css` تعریف
            | شده است. Filament CSS خودش را جدا می‌سازد، پس بدون این
            | خط تعریف فونت در پنل وجود ندارد و `font-family: Vazirmatn`
            | به فونتی نمی‌رسد (مرورگر به فونت پیش‌فرض سقوط می‌کند).
            |
            | هزینه‌ی این کار ناچیز است: `app.css` همان فایل هش‌داری
            | است که در سایت عمومی هم با کش یک‌ساله سرو می‌شود.
            */
            ->viteTheme('resources/css/app.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                ContentReadinessWidget::class,
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
