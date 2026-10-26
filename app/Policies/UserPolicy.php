<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * مجوز «کاربر» — فقط مدیر، با دو محافظ حیاتی روی حذف.
 *
 * چرا حذف کاربر ساده نیست: این حساب‌ها تنها راه ورود به پنل‌اند.
 * اگر مدیر بتواند خودش را حذف کند، یا آخرین مدیر فعال را حذف کند،
 * پنل برای همیشه قفل می‌شود و هیچ‌کس نمی‌تواند محتوا را مدیریت کند.
 * پس دو شرط اضافه بر «مدیر بودن» اعمال می‌شود:
 *
 *   ۱. هدف حذف، خودِ کاربر جاری نباشد (خودحذفی ممنوع).
 *   ۲. پس از حذف، حداقل یک مدیر فعال دیگر باقی بماند.
 *
 * محاسبه‌ی «آخرین مدیر فعال»: کاربران فعالی که `canAccessPanel()`
 * آن‌ها true است شمرده می‌شوند — یعنی همان فهرست ADMIN_EMAIL. عمداً
 * از خودِ canAccessPanel استفاده می‌کنیم تا منطق allowlist دوباره
 * نوشته نشود و دو منبع حقیقت ایجاد نشود.
 */
class UserPolicy extends AdminOnlyPolicy
{
    public function delete(User $user, Model $model): bool
    {
        if (! $this->isAdmin($user)) {
            return false;
        }

        // ۱. مدیر نباید حساب خودش را حذف کند.
        if ($user->is($model)) {
            return false;
        }

        // ۲. نباید آخرین مدیر فعال حذف شود.
        if ($model instanceof User && $this->isLastActiveAdmin($model)) {
            return false;
        }

        return true;
    }

    /**
     * آیا با حذف این کاربر، هیچ مدیر فعال دیگری نمی‌ماند؟
     */
    private function isLastActiveAdmin(User $target): bool
    {
        $remaining = User::query()
            ->where('is_active', true)
            ->whereKeyNot($target->getKey())
            ->get()
            ->filter(fn (User $candidate): bool => $candidate->canAccessPanel())
            ->count();

        return $remaining === 0;
    }
}
