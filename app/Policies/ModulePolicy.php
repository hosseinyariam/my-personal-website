<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * مجوز «ماژول» — فقط مدیر، و حذف هرگز.
 *
 * چرا `delete` همیشه false است: ماژول‌ها کلیدهای ثابت `ModuleKey`
 * هستند و seed می‌شوند. عملیات درست برای خاموش‌کردن یک بخش سایت،
 * `is_enabled = false` است — همان‌طور که مدل تصریح کرده: «خاموش‌کردن
 * ماژول داده را حذف نمی‌کند». حذف رکورد ماژول باعث می‌شود
 * `Module::isEnabled()` به حالت پیش‌فرض «روشن» برگردد و بخشی که مدیر
 * عمداً خاموش کرده بود دوباره ظاهر شود. پس حذف نه‌فقط لازم نیست،
 * بلکه معکوسِ نیت مدیر عمل می‌کند.
 */
class ModulePolicy extends AdminOnlyPolicy
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
