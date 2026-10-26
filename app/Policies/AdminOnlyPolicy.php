<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * سیاست پایه‌ی «فقط مدیر».
 *
 * ── مدل مجوزدهی MVP (تصمیم قطعی) ──────────────────────────────
 * در MVP فقط **یک نقش مدیر** وجود دارد. نقش‌های ویرایشگر/نویسنده
 * صریحاً به فازهای بعد موکول شده‌اند. پس هر متد فقط و فقط وقتی true
 * برمی‌گرداند که کاربر احراز هویت‌شده «مدیر» باشد، و تعریف مدیر همان
 * `User::canAccessPanel()` است که فهرست مجاز `ADMIN_EMAIL` را می‌سنجد.
 *
 * هشدار امنیتی: هرگز بر پایه‌ی «وارد شده است» مجوز نده. «وارد شده»
 * یعنی «حساب دارد»، نه «مدیر است». استفاده از canAccessPanel باعث
 * می‌شود ADMIN_EMAIL تنها منبع حقیقت بماند (شکست امن، نه باز).
 *
 * ⚠ این سیاست‌ها **هرگز** سایت عمومی را دروازه‌بانی نمی‌کنند. صفحات
 * عمومی با scopeهای مدل (`listable()` / `isViewable()`) کنترل
 * می‌شوند، نه با Policy. اگر روزی این Policy روی یک مسیر عمومی گذاشته
 * شود، بازدیدکننده‌ی بدون حساب همه‌چیز را از دست می‌دهد. کنترل نمایش
 * عمومی در concern `Publishable` است؛ کنترل پنل اینجاست.
 * ──────────────────────────────────────────────────────────────
 */
abstract class AdminOnlyPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, Model $model): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * تنها منبع حقیقت «مدیر بودن» — همان فهرست مجاز ADMIN_EMAIL.
     */
    protected function isAdmin(User $user): bool
    {
        return $user->canAccessPanel();
    }
}
