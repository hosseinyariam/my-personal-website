<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Widgets\ContentReadinessWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\FontProviders\LocalFontProvider;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * پنل مدیریت — «محصول داخلی» برای پرسونای مدیر محتوا (M7).
 *
 * تم با پالت UI Kit ساخته می‌شود تا تجربه‌ی بصری سایت و پنل یکپارچه باشد:
 * Brand 500 به‌عنوان primary، فونت Vazirmatn و راست‌چین بومی.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('پنل مدیریت')
            ->favicon(asset('favicon.svg'))

            // ── پالت کیت ─────────────────────────────────────
            ->colors([
                'primary' => Color::hex('#F2542D'),   // Brand 500
                'danger' => Color::hex('#B23417'),    // Brand 700 — عمقِ لهجه
                'gray' => Color::hex('#6B6F79'),      // Ink 500
                'info' => Color::hex('#FF9F6B'),      // Brand 300
                'success' => Color::hex('#F2542D'),
                'warning' => Color::hex('#FF9F6B'),
            ])
            // @font-face در همان theme.css تعریف شده؛ ارائه‌دهنده‌ی محلی چیزی اضافه نمی‌کند
            ->font('Vazirmatn', provider: LocalFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')

            ->maxContentWidth(MaxWidth::Full)
            ->sidebarCollapsibleOnDesktop()

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

            ->pages([Dashboard::class])
            ->widgets([ContentReadinessWidget::class])

            ->navigationGroups([
                'ویترین',
                'وبلاگ',
                'رزومه',
                'پیام‌ها',
                'تنظیمات',
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
