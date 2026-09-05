<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * ساختار تنظیمات + حداقلِ محتوای واقعی.
 *
 * قاعده (V3): هیچ متن ساختگی. کلیدهایی که هنوز محتوای واقعی ندارند
 * با مقدار خالی ساخته می‌شوند تا در پنل دیده و پر شوند — و صفحه‌ی
 * عمومی تا آن لحظه بخش مربوطه را مخفی نگه می‌دارد (پیوست ب).
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'profile' => [
                'display_name' => 'حسین یاری',
                'headline' => '',
                'location' => '',
                'bio_short' => '',
                'bio_long' => '',
                'expertise' => [],       // چیپ تخصص‌ها در هیرو
                'avatar' => '',
                'photo' => '',
                'resume_pdf' => '',
                'is_available' => true,
            ],
            'contact' => [
                'notification_email' => '',
                'response_note' => '',
            ],
            'seo' => [
                'meta_title' => '',
                'meta_description' => '',
                'og_image' => '',
                'matomo_url' => '',
                'matomo_site_id' => '',
            ],
        ];

        foreach ($defaults as $group => $entries) {
            foreach ($entries as $key => $value) {
                // مقدارِ واردشده از پنل هرگز بازنویسی نمی‌شود — فقط کلیدِ نبوده ساخته می‌شود.
                Setting::query()->firstOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => $value],
                );
            }
        }
    }
}
