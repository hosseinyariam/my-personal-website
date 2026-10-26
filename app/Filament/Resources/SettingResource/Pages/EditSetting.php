<?php

declare(strict_types=1);

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * ویرایش تنظیم.
 *
 * هیچ اکشن حذفی وجود ندارد: `SettingPolicy::delete` همیشه false است
 * و رندر دکمه فقط ۴۰۳ تولید می‌کند. عملیات درست، پاک‌کردن مقدار است.
 */
class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    /**
     * ذخیره از `SettingService::set()` عبور می‌کند تا محافظ کلیدهای
     * حساس اجرا شود و نوع مقدار درست ذخیره گردد.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return SettingResource::persistSetting($data, $record);
    }
}
