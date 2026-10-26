<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Services\PublishingService;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

/**
 * اکشن‌های انتشار مشترک برای منابع محتوایی.
 *
 * ── چرا انتشار هرگز با تغییر خام `status` انجام نمی‌شود ──
 * Blueprint:88-92 و Baseline §5.1 می‌گویند انتشار باید از یک مسیر
 * کنترل‌شده عبور کند. اگر این اکشن‌ها مستقیم `status` را ست کنند،
 * سه چیز دیر یا زود فراموش می‌شود:
 *   ۱. `published_at` ست نمی‌شود → scope عمومی (که fail-closed است)
 *      محتوا را نمی‌بیند و مدیر فکر می‌کند «منتشر کردم ولی دیده نمی‌شود».
 *   ۲. کش باطل نمی‌شود → کاربر تا انقضای TTL نسخه‌ی کهنه می‌بیند.
 *   ۳. زمان‌بندی آینده نرمال نمی‌شود.
 * پس هر دو اکشن `PublishingService` را صدا می‌زنند که هر سه را تضمین
 * می‌کند. این trait فقط یک لایه‌ی نازک UI روی همان سرویس است.
 */
trait PublishesContent
{
    /**
     * اکشن «انتشار».
     */
    protected static function publishAction(): Action
    {
        return Action::make('publish')
            ->label('انتشار')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('انتشار محتوا')
            ->modalDescription('این محتوا برای عموم قابل مشاهده می‌شود و زمان انتشار آن ثبت می‌گردد.')
            ->visible(fn (Model $record): bool => ! Ui::isLive($record))
            ->action(function (Model $record): void {
                app(PublishingService::class)->publish($record);

                Notification::make()
                    ->title('منتشر شد')
                    ->body('محتوا منتشر شد، زمان انتشار ثبت و کش باطل گردید.')
                    ->success()
                    ->send();
            });
    }

    /**
     * اکشن «لغو انتشار» (بازگشت به پیش‌نویس).
     *
     * برای مدل‌هایی که ستون `status` ندارند (Product/Project) سرویس
     * `published_at` را پاک می‌کند و محتوا از دید عمومی خارج می‌شود.
     */
    protected static function unpublishAction(): Action
    {
        return Action::make('unpublish')
            ->label('لغو انتشار')
            ->icon('heroicon-o-eye-slash')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('لغو انتشار')
            ->modalDescription('محتوا از دید عمومی خارج می‌شود، ولی داده‌ها حذف نمی‌شوند و می‌توانید دوباره منتشر کنید.')
            ->visible(fn (Model $record): bool => Ui::isLive($record))
            ->action(function (Model $record): void {
                app(PublishingService::class)->unpublish($record);

                Notification::make()
                    ->title('لغو انتشار شد')
                    ->body('محتوا از دید عمومی خارج شد و کش باطل گردید.')
                    ->warning()
                    ->send();
            });
    }
}
