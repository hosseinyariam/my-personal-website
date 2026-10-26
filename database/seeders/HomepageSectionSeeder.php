<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\HomepageSection;
use Illuminate\Database\Seeder;

/**
 * seed بخش‌های صفحه اصلی — هر کلید در `HomepageSection::ALLOWED_KEYS`.
 *
 * ترتیب اینجا همان ترتیب موردنظر روایت صفحه اصلی است. `module_key`
 * تنها برای بخش‌هایی ست می‌شود که ماژول بالادستی دارند؛ اگر ماژول
 * خاموش باشد، `scopeVisible` خودش بخش را پنهان می‌کند.
 *
 * نکته: `current_focus` هم ماژول اختصاصی دارد، پس به همان ماژول وصل
 * می‌شود (سه بخش هویت‌محور hero / how_i_think / contact_cta ماژول
 * مستقل ندارند و null می‌مانند).
 *
 * ── قاعده‌ی idempotency ──
 * کلید طبیعی `key` است. `is_enabled` فقط در لحظه‌ی ساخت ست می‌شود تا
 * خاموش‌کردن عمدی یک بخش توسط مدیر، با اجرای دوباره‌ی seed برنگردد.
 */
class HomepageSectionSeeder extends Seeder
{
    /**
     * ترتیب قطعی بخش‌ها با پیکربندی هرکدام.
     *
     * @var array<string, array{module: ?string, limit: ?int}>
     */
    private const SECTIONS = [
        'hero' => ['module' => null, 'limit' => null],
        'current_focus' => ['module' => 'current_focus', 'limit' => null],
        'featured_projects' => ['module' => 'projects', 'limit' => 3],
        'featured_products' => ['module' => 'products', 'limit' => 3],
        'how_i_think' => ['module' => null, 'limit' => null],
        'selected_notes' => ['module' => 'notes', 'limit' => 3],
        'journey_preview' => ['module' => 'timeline', 'limit' => 5],
        'biography_preview' => ['module' => 'biography', 'limit' => null],
        'contact_cta' => ['module' => null, 'limit' => null],
    ];

    public function run(): void
    {
        $sortOrder = 0;

        foreach (self::SECTIONS as $key => $config) {
            $section = HomepageSection::query()->firstOrNew(['key' => $key]);

            if (! $section->exists) {
                // فقط در ساخت — تصمیم روشن/خاموش مدیر حفظ می‌شود.
                $section->is_enabled = true;
            }

            $section->title = HomepageSection::ALLOWED_KEYS[$key] ?? $key;
            $section->module_key = $config['module'];
            $section->display_limit = $config['limit'];
            $section->sort_order = $sortOrder++;

            $section->save();
        }
    }
}
