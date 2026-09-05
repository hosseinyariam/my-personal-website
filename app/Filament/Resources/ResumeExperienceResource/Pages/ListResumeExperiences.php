<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResumeExperienceResource\Pages;

use App\Filament\Resources\ResumeExperienceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResumeExperiences extends ListRecords
{
    protected static string $resource = ResumeExperienceResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('سابقه‌ی جدید')];
    }
}
