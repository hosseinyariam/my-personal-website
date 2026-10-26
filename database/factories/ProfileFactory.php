<?php

declare(strict_types=1);

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * پروفایل با داده‌ی واقعی آزمایشی.
 *
 * توجه: این factory برای تست است، نه برای seed تولید. هیچ‌کدام از این
 * مقادیر نباید روی سایت عمومی بروند — تست‌ها در دیتابیس in-memory
 * اجرا می‌شوند و seed تولید فقط از `ProductionSeeder` می‌آید.
 */
return new class extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'display_name' => 'تست کاربر',
            'professional_title' => 'Software Engineer & Technology Entrepreneur',
            'short_bio' => 'توضیح کوتاه آزمایشی برای تست.',
            'biography' => 'متن بیوگرافی آزمایشی.',
            'positioning_statement' => 'جمله‌ی موضع‌گیری آزمایشی.',
            'philosophy' => 'فلسفه‌ی آزمایشی: مسئله را بفهم، بساز، یاد بگیر.',
            'core_values' => ['صداقت', 'مسئولیت‌پذیری'],
            'current_mission' => 'مأموریت آزمایشی.',
            'availability_status' => 'آماده‌ی همکاری',
            'email_public' => 'test@example.com',
            'is_active' => true,
        ];
    }
};
