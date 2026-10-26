<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomepageSectionResource\Pages;

use App\Filament\Resources\HomepageSectionResource;
use Filament\Resources\Pages\EditRecord;

/**
 * ویرایش بخش صفحه اصلی.
 *
 * هیچ اکشن حذفی وجود ندارد: `HomepageSectionPolicy::delete` همیشه
 * false است و رندر دکمه فقط ۴۰۳ تولید می‌کند. عملیات درست برای
 * کنار گذاشتن بخش، خاموش‌کردن `is_enabled` است.
 */
class EditHomepageSection extends EditRecord
{
    protected static string $resource = HomepageSectionResource::class;
}
