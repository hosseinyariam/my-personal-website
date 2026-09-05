<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('مشاهده در سایت')
                ->icon('heroicon-m-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => route('blog.show', $this->getRecord()->slug))
                ->openUrlInNewTab()
                // فقط پستی که واقعاً عمومی شده قابل بازدید است
                ->visible(fn () => $this->getRecord()->isPublished()),

            DeleteAction::make(),
        ];
    }
}
