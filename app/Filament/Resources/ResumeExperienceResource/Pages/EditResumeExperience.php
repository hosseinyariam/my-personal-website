<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResumeExperienceResource\Pages;

use App\Filament\Resources\ResumeExperienceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResumeExperience extends EditRecord
{
    protected static string $resource = ResumeExperienceResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
