<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResumeCertificateResource\Pages;

use App\Filament\Resources\ResumeCertificateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResumeCertificates extends ListRecords
{
    protected static string $resource = ResumeCertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('گواهی جدید')];
    }
}
