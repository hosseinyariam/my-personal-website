<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResumeSkillResource\Pages;

use App\Filament\Resources\ResumeSkillResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResumeSkills extends ListRecords
{
    protected static string $resource = ResumeSkillResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('مهارت جدید')];
    }
}
