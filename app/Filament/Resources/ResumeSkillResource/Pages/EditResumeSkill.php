<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResumeSkillResource\Pages;

use App\Filament\Resources\ResumeSkillResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResumeSkill extends EditRecord
{
    protected static string $resource = ResumeSkillResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
