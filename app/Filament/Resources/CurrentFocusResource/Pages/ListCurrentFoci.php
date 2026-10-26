<?php

namespace App\Filament\Resources\CurrentFocusResource\Pages;

use App\Filament\Resources\CurrentFocusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCurrentFoci extends ListRecords
{
    protected static string $resource = CurrentFocusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
