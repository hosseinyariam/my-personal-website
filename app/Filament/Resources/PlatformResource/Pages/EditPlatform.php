<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlatformResource\Pages;

use App\Filament\Resources\PlatformResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlatform extends EditRecord
{
    protected static string $resource = PlatformResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // بازدید صفحه‌ی عمومی — فقط وقتی رکورد واقعاً منتشر شده است
            Action::make('view')
                ->label('مشاهده در سایت')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => route('platforms.show', $this->getRecord()->slug))
                ->openUrlInNewTab()
                ->visible(fn () => $this->getRecord()->is_active),

            DeleteAction::make(),
        ];
    }
}
