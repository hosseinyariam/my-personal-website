<?php

declare(strict_types=1);

namespace App\Policies;

/**
 * مجوز «ContactMessage» — فقط مدیر (مدل تک‌نقشی MVP).
 *
 * رفتار از AdminOnlyPolicy می‌آید: هر عملیات پنل تنها برای کاربری
 * مجاز است که canAccessPanel() او true باشد، یعنی ایمیلش در فهرست
 * ADMIN_EMAIL باشد و حسابش فعال باشد.
 *
 * ⚠ این Policy فقط دروازه‌ی پنل است. نمایش عمومی «ContactMessage» با scopeهای
 * مدل (listable() / isViewable()) کنترل می‌شود، نه با Policy.
 */
class ContactMessagePolicy extends AdminOnlyPolicy {}
