<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ModuleKey;
use App\Models\Module;
use Illuminate\Database\Seeder;

/**
 * seed ماژول‌ها — هر ۱۲ کلید `ModuleKey`.
 *
 * ── قاعده‌ی idempotency (تصمیم مستندات) ──
 * این seeder باید بی‌خطر بارها اجرا شود. دو نکته:
 *   ۱. کلید طبیعی `key` است؛ با `firstOrNew` رکورد تکراری ساخته نمی‌شود.
 *   ۲. `is_enabled` **فقط در لحظه‌ی ساخت** ست می‌شود. دلیل: مدیر ممکن
 *      است عمداً ماژولی را خاموش کرده باشد؛ اگر seed دوباره آن را روشن
 *      کند، تصمیم مدیر بازنویسی می‌شود. این همان «عدم بازنویسی محتوای
 *      ویرایش‌شده توسط مدیر» است که مستندات الزام کرده.
 */
class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ModuleKey::cases() as $index => $key) {
            $module = Module::query()->firstOrNew(['key' => $key->value]);

            if (! $module->exists) {
                // فقط در ساخت — نه در به‌روزرسانی.
                $module->is_enabled = true;
            }

            $module->name = $key->label();
            $module->sort_order = $index;

            $module->save();
        }
    }
}
