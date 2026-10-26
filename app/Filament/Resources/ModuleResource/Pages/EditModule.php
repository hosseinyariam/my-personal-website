<?php

declare(strict_types=1);

namespace App\Filament\Resources\ModuleResource\Pages;

use App\Filament\Resources\ModuleResource;
use Filament\Resources\Pages\EditRecord;

/**
 * ویرایش ماژول.
 *
 * هیچ اکشن حذفی وجود ندارد: `ModulePolicy::delete` همیشه false است،
 * و حذف رکورد ماژول باعث می‌شود `Module::isEnabled()` به پیش‌فرض
 * «روشن» برگردد — یعنی معکوسِ نیت مدیر. عملیات درست، خاموش‌کردن است.
 */
class EditModule extends EditRecord
{
    protected static string $resource = ModuleResource::class;
}
