<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResumeEducationResource\Pages;

use App\Filament\Resources\ResumeEducationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResumeEducation extends EditRecord
{
    protected static string $resource = ResumeEducationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
