<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * مجوز «تنظیمات» — فقط مدیر، و حذف هرگز.
 *
 * چرا `delete` همیشه false است: کلیدهای تنظیمات مجموعه‌ای معلوم و
 * seedشده‌اند (`site.tagline`، `seo.default_description`، …). حذف یک
 * کلید، آن را از دیتابیس برمی‌دارد ولی کدِ خواننده‌اش همچنان انتظارش
 * را دارد؛ نتیجه یک نقص خاموش در سایت عمومی است که تا وقتی کسی آن
 * صفحه را باز نکند دیده نمی‌شود. عملیات درست برای «غیرفعال‌کردن یک
 * تنظیم»، پاک‌کردن مقدار یا تغییر `is_public` است، نه حذف رکورد.
 */
class SettingPolicy extends AdminOnlyPolicy
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
