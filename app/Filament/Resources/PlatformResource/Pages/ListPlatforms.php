<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlatformResource\Pages;

use App\Filament\Resources\PlatformResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlatforms extends ListRecords
{
    protected static string $resource = PlatformResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('پلتفرم جدید')];
    }
}
