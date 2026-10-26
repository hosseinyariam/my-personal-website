<?php

declare(strict_types=1);

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    /**
     * ایجاد از `SettingService::set()` عبور می‌کند تا محافظ کلیدهای
     * حساس اجرا شود. سرویس روی `key` با updateOrCreate کار می‌کند،
     * پس کلید تکراری هم به‌درستی مدیریت می‌شود.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        return SettingResource::persistSetting($data);
    }
}
