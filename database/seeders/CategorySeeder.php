<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * seed دسته‌های یادداشت — هفت حوزه‌ی IA بخش ۹.
 *
 * منبع: `docs/Information Architecture & User Journey v1.0`، بخش ۹:
 * Building Products, Problem Solving, Software Engineering, AI,
 * Entrepreneurship, Lessons Learned, Personal Growth.
 *
 * تصمیم D10: این هفت حوزه به‌عنوان دسته‌های اولیه seed می‌شوند، ولی
 * چون جدول‌اند، تغییرشان کد نمی‌خواهد.
 *
 * ── idempotency ──
 * کلید طبیعی `name` است (نام فارسی، پایدار). `sort_order` همیشه
 * همگام می‌شود چون ترتیب نمایش، محتوای ویرایش‌شده‌ی مدیر نیست.
 * Slug اگر خالی بماند در مدل از نام ساخته می‌شود.
 */
class CategorySeeder extends Seeder
{
    /**
     * نام‌های فارسی دسته‌ها به ترتیب IA.
     *
     * @var list<string>
     */
    private const CATEGORIES = [
        'ساخت محصول',
        'حل مسئله',
        'مهندسی نرم‌افزار',
        'هوش مصنوعی',
        'کارآفرینی',
        'درس‌آموخته‌ها',
        'رشد شخصی',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $index => $name) {
            Category::query()->updateOrCreate(
                ['name' => $name],
                ['sort_order' => $index],
            );
        }
    }
}
