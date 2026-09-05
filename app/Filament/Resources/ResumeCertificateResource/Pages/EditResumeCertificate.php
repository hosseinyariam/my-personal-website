<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResumeCertificateResource\Pages;

use App\Filament\Resources\ResumeCertificateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResumeCertificate extends EditRecord
{
    protected static string $resource = ResumeCertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
