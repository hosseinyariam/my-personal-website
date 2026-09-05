<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResumeEducationResource\Pages;

use App\Filament\Resources\ResumeEducationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResumeEducations extends ListRecords
{
    protected static string $resource = ResumeEducationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('تحصیلات جدید')];
    }
}
