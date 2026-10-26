<?php

declare(strict_types=1);

use App\Enums\CurrentFocusType;
use App\Models\Company;
use App\Models\ContactChannel;
use App\Models\CurrentFocus;
use App\Models\Education;
use App\Models\Experience;
use App\Models\HomepageSection;
use App\Models\Module;
use App\Models\Note;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Technology;
use App\Models\TimelineEvent;

/**
 * ساخت داده‌ی واقعی برای تست صفحات.
 *
 * چرا این helper وجود دارد: تست صفحات عمومی نباید فقط کد وضعیت را
 * بسنجد. صفحه‌ی خالی هم ۲۰۰ برمی‌گرداند. این تابع داده‌ی واقعی
 * می‌سازد تا تست بتواند بررسی کند که محتوا **واقعاً رندر شده** است.
 */
function seedPublicSite(): void
{
    // ماژول‌ها — همه روشن (حالت تولید پس از seed)
    foreach (['biography', 'projects', 'products', 'companies', 'notes', 'timeline'] as $key) {
        Module::create(['key' => $key, 'name' => $key, 'is_enabled' => true]);
    }

    // بخش‌های صفحه اصلی — همه فعال
    foreach (['hero', 'current_focus', 'featured_projects', 'contact_cta'] as $i => $key) {
        HomepageSection::create([
            'key' => $key,
            'module_key' => match ($key) {
                'featured_projects' => 'projects',
                'contact_cta' => null,
                default => null,
            },
            'is_enabled' => true,
            'sort_order' => $i,
        ]);
    }

    Profile::create([
        'display_name' => 'حسین یاریم',
        'professional_title' => 'Software Engineer & Technology Entrepreneur',
        'short_bio' => 'مهندس نرم‌افزار و کارآفرین فناوری.',
        'biography' => 'متن بیوگرافی واقعی برای تست.',
        'positioning_statement' => 'مسائل واقعی را به محصولات کاربردی تبدیل می‌کنم.',
        'philosophy' => 'مسئله را بفهم. بساز. یاد بگیر. ادامه بده.',
        'core_values' => ['صداقت', 'مسئولیت‌پذیری'],
        'current_mission' => 'ساختن محصولات کاربردی.',
        'availability_status' => 'آماده‌ی همکاری',
        'email_public' => 'hello@example.com',
        'is_active' => true,
    ]);

    $company = Company::create([
        'name' => 'شرکت نمونه',
        'summary' => 'یک شرکت فناوری.',
        'relationship_type' => 'founder',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $product = Product::create([
        'name' => 'محصول نمونه',
        'tagline' => 'شعار محصول.',
        'summary' => 'خلاصه‌ی محصول.',
        'problem' => 'مسئله‌ای که حل می‌کند.',
        'solution' => 'راه‌حل ارائه‌شده.',
        'status' => 'live',
        'published_at' => now(),
        'is_featured' => true,
    ]);

    $technology = Technology::create(['name' => 'Laravel']);
    $skill = Skill::create(['name' => 'معماری نرم‌افزار', 'category' => 'مهندسی']);

    $project = Project::create([
        'title' => 'پروژه نمونه',
        'summary' => 'خلاصه‌ی پروژه.',
        'problem' => 'مسئله‌ی پروژه.',
        'context' => 'زمینه‌ی پروژه.',
        'role' => 'مهندس اصلی',
        'contribution' => 'مشارکت من.',
        'outcome' => 'نتیجه‌ی پروژه.',
        'lessons_learned' => 'آموخته‌ی مهم پروژه.',
        'status' => 'completed',
        'visibility' => 'public',
        'started_at' => now()->subYear(),
        'completed_at' => now(),
        'published_at' => now(),
        'is_featured' => true,
    ]);

    // اتصال‌ها — شواهد واقعی برای نمایش مهارت و فناوری
    $project->technologies()->attach($technology->id);
    $project->skills()->attach($skill->id);
    $project->companies()->attach($company->id);
    $product->projects()->attach($project->id);
    $company->products()->attach($product->id);

    Note::create([
        'title' => 'یادداشت نمونه',
        'excerpt' => 'خلاصه‌ی یادداشت.',
        'body' => 'متن کامل یادداشت برای تست.',
        'type' => 'article',
        'status' => 'published',
        'visibility' => 'public',
        'published_at' => now(),
        'is_featured' => true,
    ]);

    TimelineEvent::create([
        'title' => 'رویداد نمونه',
        'summary' => 'توضیح رویداد.',
        'event_type' => 'milestone',
        'occurred_at' => now()->subMonths(3),
        'status' => 'published',
        'visibility' => 'public',
        'published_at' => now(),
        'is_highlight' => true,
    ]);

    CurrentFocus::create([
        'title' => 'در حال ساخت محصول جدید',
        'description' => 'توضیح تمرکز فعلی.',
        'focus_type' => 'building',
        'status' => 'active',
        'is_featured' => true,
    ]);

    Experience::create([
        'company_id' => $company->id,
        'title' => 'مهندس نرم‌افزار',
        'employment_type' => 'full_time',
        'started_at' => now()->subYears(3),
        'is_current' => true,
        'status' => 'published',
    ]);

    Education::create([
        'institution' => 'دانشگاه نمونه',
        'degree' => 'کارشناسی',
        'field_of_study' => 'مهندسی کامپیوتر',
        'started_at' => now()->subYears(8),
        'ended_at' => now()->subYears(4),
        'status' => 'published',
    ]);

    ContactChannel::create([
        'label' => 'ایمیل',
        'channel_type' => 'email',
        'url' => 'hello@example.com',
        'purpose' => 'professional',
        'is_public' => true,
        'is_active' => true,
    ]);
}
