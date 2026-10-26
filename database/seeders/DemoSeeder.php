<?php

declare(strict_types=1);

namespace Database\Seeders;

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
use Illuminate\Database\Seeder;

/**
 * DemoSeeder — داده‌ی نمایشی برای توسعه‌ی محلی و اسکرین‌شات.
 *
 * ⚠⚠ هشدار امنیتی/محتوایی ⚠⚠
 * این seeder **هرگز** نباید روی سایت زنده اجرا شود. محتوای آن ساختگی
 * است و قاعده‌ی سخت برند («هیچ ادعای بی‌پشتوانه») را نقض می‌کند.
 * به همین دلیل یک گارد محیطی دارد: اگر محیط `production` باشد، با
 * پیام خطای فارسی متوقف می‌شود. این «نرده‌ی ایمنی» است تا کسی
 * به‌اشتباه محتوای جعلی روی سایت واقعی نریزد.
 *
 * شکل داده عمداً با `tests/helpers.php::seedPublicSite()` هم‌خوان است
 * تا محیط توسعه‌ی محلی و تست، یک ساختار داشته باشند. همه‌ی نام‌ها با
 * پیشوند «[دمو]» علامت‌گذاری شده‌اند تا در هیچ شرایطی با محتوای واقعی
 * اشتباه نشوند.
 *
 * idempotency: هر رکورد با کلید طبیعی خود (name/title/label) upsert
 * می‌شود، پس اجرای دوباره رکورد تکراری نمی‌سازد.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── نرده‌ی ایمنی: هرگز روی production ──
        if (app()->environment('production')) {
            $this->command?->error(
                'DemoSeeder روی محیط production اجرا نمی‌شود. این seeder محتوای ساختگی می‌سازد '
                .'و سایت زنده باید فقط محتوای واقعی داشته باشد. برای محیط تولید از ProductionSeeder استفاده کنید.'
            );

            return;
        }

        $this->seedModulesAndSections();
        $this->seedProfile();
        $this->seedPortfolio();
        $this->seedNotes();
        $this->seedJourney();
        $this->seedContactChannels();

        $this->command?->info('DemoSeeder: داده‌ی نمایشی (با پیشوند «[دمو]») ساخته/به‌روزرسانی شد — فقط برای توسعه‌ی محلی.');
    }

    /**
     * ماژول‌ها و بخش‌های صفحه اصلی — اگر ProductionSeeder اجرا نشده
     * باشد، اینجا با همان منطق idempotent ساخته می‌شوند.
     */
    private function seedModulesAndSections(): void
    {
        // اگر DatabaseSeeder اجرا شده، این‌ها از قبل هستند و دست نمی‌خورند.
        if (Module::query()->doesntExist()) {
            $this->call(ModuleSeeder::class);
        }

        if (HomepageSection::query()->doesntExist()) {
            $this->call(HomepageSectionSeeder::class);
        }
    }

    private function seedProfile(): void
    {
        $profile = Profile::query()->firstOrNew(['display_name' => '[دمو] حسین یاریم']);

        $profile->fill([
            'professional_title' => 'Software Engineer & Technology Entrepreneur',
            'short_bio' => 'مهندس نرم‌افزار و کارآفرین فناوری.',
            'biography' => 'متن بیوگرافی نمایشی برای توسعه‌ی محلی و اسکرین‌شات.',
            'positioning_statement' => 'مسائل واقعی را به محصولات کاربردی تبدیل می‌کنم.',
            'philosophy' => 'مسئله را بفهم. بساز. یاد بگیر. ادامه بده.',
            'core_values' => ['صداقت', 'مسئولیت‌پذیری'],
            'current_mission' => 'ساختن محصولات کاربردی.',
            'availability_status' => 'آماده‌ی همکاری',
            'email_public' => 'hello@example.com',
            'is_active' => true,
        ]);

        $profile->save();
    }

    private function seedPortfolio(): void
    {
        $company = Company::query()->firstOrNew(['name' => '[دمو] شرکت نمونه']);
        $company->fill([
            'summary' => 'یک شرکت فناوری نمایشی.',
            'relationship_type' => 'founder',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $company->save();

        $product = Product::query()->firstOrNew(['name' => '[دمو] محصول نمونه']);
        $product->fill([
            'tagline' => 'شعار محصول نمایشی.',
            'summary' => 'خلاصه‌ی محصول نمایشی.',
            'problem' => 'مسئله‌ای که حل می‌کند.',
            'solution' => 'راه‌حل ارائه‌شده.',
            'status' => 'live',
            'published_at' => now(),
            'is_featured' => true,
        ]);
        $product->save();

        $technology = Technology::query()->firstOrCreate(['name' => 'Laravel']);
        $skill = Skill::query()->firstOrCreate(
            ['name' => '[دمو] معماری نرم‌افزار'],
            ['category' => 'مهندسی'],
        );

        $project = Project::query()->firstOrNew(['title' => '[دمو] پروژه نمونه']);
        $project->fill([
            'summary' => 'خلاصه‌ی پروژه‌ی نمایشی.',
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
        $project->save();

        // اتصال‌ها با syncWithoutDetaching تا اجرای دوباره pivot تکراری نسازد.
        $project->technologies()->syncWithoutDetaching([$technology->id]);
        $project->skills()->syncWithoutDetaching([$skill->id]);
        $project->companies()->syncWithoutDetaching([$company->id]);
        $product->projects()->syncWithoutDetaching([$project->id]);
        $company->products()->syncWithoutDetaching([$product->id]);
    }

    private function seedNotes(): void
    {
        Note::query()->updateOrCreate(
            ['title' => '[دمو] یادداشت نمونه'],
            [
                'excerpt' => 'خلاصه‌ی یادداشت نمایشی.',
                'body' => 'متن کامل یادداشت نمایشی برای توسعه‌ی محلی.',
                'type' => 'article',
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => now(),
                'is_featured' => true,
            ],
        );
    }

    private function seedJourney(): void
    {
        TimelineEvent::query()->updateOrCreate(
            ['title' => '[دمو] رویداد نمونه'],
            [
                'summary' => 'توضیح رویداد نمایشی.',
                'event_type' => 'milestone',
                'occurred_at' => now()->subMonths(3),
                'status' => 'published',
                'visibility' => 'public',
                'published_at' => now(),
                'is_highlight' => true,
            ],
        );

        CurrentFocus::query()->updateOrCreate(
            ['title' => '[دمو] در حال ساخت محصول جدید'],
            [
                'description' => 'توضیح تمرکز فعلی نمایشی.',
                'focus_type' => 'building',
                'status' => 'active',
                'is_featured' => true,
            ],
        );

        $company = Company::query()->where('name', '[دمو] شرکت نمونه')->first();

        Experience::query()->updateOrCreate(
            ['title' => '[دمو] مهندس نرم‌افزار', 'company_id' => $company?->id],
            [
                'employment_type' => 'full_time',
                'started_at' => now()->subYears(3),
                'is_current' => true,
                'status' => 'published',
            ],
        );

        Education::query()->updateOrCreate(
            ['institution' => '[دمو] دانشگاه نمونه', 'degree' => 'کارشناسی'],
            [
                'field_of_study' => 'مهندسی کامپیوتر',
                'started_at' => now()->subYears(8),
                'ended_at' => now()->subYears(4),
                'status' => 'published',
            ],
        );
    }

    private function seedContactChannels(): void
    {
        ContactChannel::query()->updateOrCreate(
            ['label' => '[دمو] ایمیل'],
            [
                'channel_type' => 'email',
                'url' => 'hello@example.com',
                'purpose' => 'professional',
                'is_public' => true,
                'is_active' => true,
            ],
        );
    }
}
