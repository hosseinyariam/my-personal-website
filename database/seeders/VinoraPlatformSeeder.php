<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PlatformStatus;
use App\Models\Platform;
use Illuminate\Database\Seeder;

/**
 * وینورا — اولین و تنها رکورد واقعی ویترین در لحظه‌ی لانچ (تصمیم D05).
 *
 * متن‌ها عیناً از بخش M3 سند PRD آمده‌اند و پیش‌نویس مدیر محصول‌اند؛
 * از پنل قابل ویرایش‌اند و ویرایشِ بعدی با اجرای دوباره‌ی seeder بازنویسی نمی‌شود.
 */
class VinoraPlatformSeeder extends Seeder
{
    public function run(): void
    {
        Platform::query()->firstOrCreate(
            ['slug' => 'vinora'],
            [
                'title' => 'وینورا',
                'tagline' => 'پلتفرم وبِ هوشمند سلامت تاکستان',

                'problem' => 'تشخیص دیرهنگام و حدسیِ بیماری‌های رایج انگور، وابستگی به دسترسی محدود به کارشناس گیاه‌پزشکی و درمان غیرهدفمند که به خسارت برداشت می‌انجامد.',

                'solution' => 'شناسایی بیماری از روی تصویر و دریافت بلافاصله‌ی شناسنامه‌ی علمی بیماری + روش پیشگیری و کنترل.',

                'status' => PlatformStatus::Live,
                'external_url' => 'https://vinora.hosseinyariam.ir/',
                'icon' => 'plant-2',

                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 0,
            ],
        );
    }
}
