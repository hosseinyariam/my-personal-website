<?php

declare(strict_types=1);

namespace App\Filament\Resources\SocialLinkResource\Pages;

use App\Filament\Resources\SocialLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSocialLinks extends ListRecords
{
    protected static string $resource = SocialLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('لینک جدید')];
    }
}
