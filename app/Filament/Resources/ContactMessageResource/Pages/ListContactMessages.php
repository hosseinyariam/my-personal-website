<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactMessageResource\Pages;

use App\Filament\Resources\ContactMessageResource;
use Filament\Resources\Pages\ListRecords;

/**
 * صندوق پیام‌های تماس.
 *
 * هیچ اکشن «ایجاد» وجود ندارد: پیام تماس فقط از فرم عمومی می‌آید و
 * ساخت دستی، سابقه‌ی واقعی مکاتبه را آلوده می‌کند.
 */
class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
