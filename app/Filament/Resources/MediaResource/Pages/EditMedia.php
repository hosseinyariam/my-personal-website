<?php

declare(strict_types=1);

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Services\MediaService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/**
 * ویرایش فراداده‌ی رسانه.
 *
 * حذف از `MediaService` عبور می‌کند تا ابتدا پیوست‌ها و رکورد، سپس
 * فایل فیزیکی (فقط اگر جای دیگری استفاده نشده) پاک شود. مسیر و دیسک
 * قابل ویرایش نیستند.
 */
class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف')
                ->modalHeading('حذف رسانه')
                ->modalDescription(fn ($record): string => $record->isShared()
                    ? "این رسانه در {$record->usageCount()} جا استفاده شده است. حذف آن محتوای دیگر را می‌شکند."
                    : 'این رسانه جای دیگری استفاده نشده و با حذف آن، فایل هم پاک می‌شود.')
                ->using(function ($record): void {
                    app(MediaService::class)->delete($record, force: $record->isShared());
                }),
        ];
    }
}
