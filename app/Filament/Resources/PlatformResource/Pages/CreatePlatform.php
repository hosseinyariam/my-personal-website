<?php

declare(strict_types=1);

namespace App\Filament\Resources\PlatformResource\Pages;

use App\Filament\Resources\PlatformResource;
use App\Support\Slugger;
use Filament\Resources\Pages\CreateRecord;

class CreatePlatform extends CreateRecord
{
    protected static string $resource = PlatformResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // تور ایمنی: اگر اسلاگ خالی ماند یا تکراری بود، خودمان یکتا می‌سازیم.
        $data['slug'] = Slugger::unique(
            filled($data['slug'] ?? null) ? $data['slug'] : ($data['title'] ?? ''),
            \App\Models\Platform::class,
        );

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
