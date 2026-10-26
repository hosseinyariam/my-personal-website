<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * مجوز «بخش صفحه اصلی» — فقط مدیر، و حذف هرگز.
 *
 * چرا `delete` همیشه false است: بخش‌ها از allowlist ثابت
 * `HomepageSection::ALLOWED_KEYS` می‌آیند و فقط **ترتیب و پیکربندی**
 * نگه می‌دارند؛ محتوا در جدول خودش است. عملیات درست برای کنار گذاشتن
 * یک بخش، `is_enabled = false` است که هیچ داده‌ای را حذف نمی‌کند و با
 * روشن‌کردن دوباره برمی‌گردد. حذف رکورد، بخش را از allowlist بیرون
 * نمی‌برد و فقط باعث می‌شود Composer صفحه اصلی به حالت پیش‌فرض برگردد؛
 * یعنی حذف بی‌اثر و گمراه‌کننده است.
 */
class HomepageSectionPolicy extends AdminOnlyPolicy
{
    public function delete(User $user, Model $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return false;
    }
}
