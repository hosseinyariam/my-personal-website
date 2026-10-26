<?php

namespace App\Filament\Resources\CurrentFocusResource\Pages;

use App\Filament\Resources\CurrentFocusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCurrentFocus extends EditRecord
{
    protected static string $resource = CurrentFocusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
