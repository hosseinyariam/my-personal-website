<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Module;
use App\Models\Note;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Support\Facades\Cache;

/**
 * صفحات عمومی — بررسی واقعی رندر.
 *
 * ── فلسفه‌ی این تست ──
 * صفحه‌ی خالی هم کد ۲۰۰ برمی‌گرداند. پس assert روی status کافی
 * نیست؛ اینجا بررسی می‌شود که محتوای واقعی در HTML هست. این همان
 * الزام دستورالعمل است: «صرفاً سبز بودن تست‌ها کافی نیست».
 */
beforeEach(function (): void {
    seedPublicSite();
    Cache::flush(); // کش ماژول و صفحه اصلی باید خالی باشد تا داده‌ی تازه خوانده شود
});

// ── صفحه اصلی ──

it('صفحه اصلی هویت و محتوای واقعی را نشان می‌دهد', function (): void {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('حسین یاریم')
        ->assertSee('Software Engineer & Technology Entrepreneur');

    // بخش‌های صفحه اصلی باید واقعاً رندر شوند، نه فقط هدر و فوتر
    $response->assertSee('در حال ساخت محصول جدید')   // تمرکز فعلی
        ->assertSee('پروژه نمونه');                    // پروژه‌ی منتخب
});

it('صفحه اصلی عنوان و متادیتای درست دارد', function (): void {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('<title>', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee('og:title', false);
});

// ── بیوگرافی ──

it('صفحه بیوگرافی تجربه و تحصیلات واقعی را نشان می‌دهد', function (): void {
    $response = $this->get('/biography');

    $response->assertOk()
        ->assertSee('متن بیوگرافی واقعی برای تست.')
        ->assertSee('مهندس نرم‌افزار')      // تجربه
        ->assertSee('دانشگاه نمونه');        // تحصیلات
});

it('مهارت فقط با شاهد واقعی نمایش داده می‌شود', function (): void {
    /*
    | این تست یک قاعده‌ی محصولی را می‌سنجد: «Evidence Over Claims».
    | مهارتی که به هیچ پروژه‌ی عمومی وصل نیست، ادعای بی‌پشتوانه است
    | و نباید در سایت دیده شود.
    */

    // مهارت متصل به پروژه‌ی عمومی — باید نمایش داده شود
    $this->get('/biography')->assertOk()->assertSee('معماری نرم‌افزار');

    // مهارت بدون هیچ پروژه — نباید نمایش داده شود
    Skill::create(['name' => 'مهارت-بی-شاهد-یکتا']);

    $this->get('/biography')->assertOk()->assertDontSee('مهارت-بی-شاهد-یکتا');
});

it('مهارت پروژه منتشرنشده را به عنوان شاهد نمی‌پذیرد', function (): void {
    // پروژه‌ای که مهارت دارد ولی منتشر نشده است
    $project = Project::create([
        'title' => 'پروژه منتشرنشده',
        'status' => 'completed',
        'visibility' => 'public',
        'published_at' => null, // کلید ماجرا
    ]);

    $skill = Skill::create(['name' => 'مهارت-فقط-در-پیش‌نویس']);
    $project->skills()->attach($skill->id);

    $this->get('/biography')->assertOk()->assertDontSee('مهارت-فقط-در-پیش‌نویس');
});

// ── پروژه‌ها ──

it('فهرست پروژه‌ها کارت واقعی رندر می‌کند', function (): void {
    $this->get('/projects')
        ->assertOk()
        ->assertSee('پروژه نمونه')
        ->assertSee('تکمیل‌شده'); // برچسب وضعیت
});

it('جزئیات پروژه همه‌ی بخش‌های پرونده را نشان می‌دهد', function (): void {
    $project = Project::query()->firstOrFail();

    $this->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('مسئله')
        ->assertSee('زمینه')
        ->assertSee('آموخته‌ها')
        ->assertSee('آموخته‌ی مهم پروژه.');
});

it('پروژه پیش‌نویس با slug شناخته‌شده هم لو نمی‌رود', function (): void {
    // محافظت در سطح مدل، نه فقط در Query فهرست
    $draft = Project::create([
        'title' => 'پروژه محرمانه',
        'status' => 'completed',
        'visibility' => 'public',
        'published_at' => null, // منتشر نشده
    ]);

    $this->get(route('projects.show', $draft))->assertNotFound();
});

it('پروژه خصوصی حتی با تاریخ انتشار لو نمی‌رود', function (): void {
    $private = Project::create([
        'title' => 'پروژه خصوصی',
        'status' => 'completed',
        'visibility' => 'private',
        'published_at' => now(),
    ]);

    $this->get(route('projects.show', $private))->assertNotFound();
});

// ── محصولات ──

it('جزئیات محصول هشدار پیش‌از‌عرضه را برای محصول در حال توسعه نشان می‌دهد', function (): void {
    $dev = Product::create([
        'name' => 'محصول در حال توسعه',
        'summary' => 'خلاصه.',
        'status' => 'development',
        'published_at' => now(),
    ]);

    $this->get(route('products.show', $dev))
        ->assertOk()
        // الزام: قابلیت برنامه‌ریزی‌شده نباید آماده معرفی شود
        ->assertSee('در حال توسعه');
});

it('محصول فعال برچسب فعال دارد', function (): void {
    $product = Product::query()->where('status', 'live')->firstOrFail();

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('فعال');
});

// ── شرکت‌ها ──

it('فهرست و جزئیات شرکت رندر می‌شود', function (): void {
    $company = Company::query()->firstOrFail();

    $this->get('/companies')->assertOk()->assertSee('شرکت نمونه');
    $this->get(route('companies.show', $company))->assertOk()->assertSee('یک شرکت فناوری.');
});

// ── یادداشت‌ها ──

it('یادداشت زمان مطالعه و دسته را نشان می‌دهد', function (): void {
    $note = Note::query()->firstOrFail();

    $this->get('/notes')
        ->assertOk()
        ->assertSee('یادداشت نمونه');

    $this->get(route('notes.show', $note))
        ->assertOk()
        ->assertSee('متن کامل یادداشت برای تست.')
        ->assertSee('دقیقه مطالعه');
});

// ── مسیر حرفه‌ای ──

it('مسیر حرفه‌ای رویدادهای انتخاب‌شده را نشان می‌دهد', function (): void {
    $this->get('/journey')
        ->assertOk()
        ->assertSee('رویداد نمونه');
});

// ── تماس ──

it('صفحه تماس کانال‌های واقعی را از دیتابیس می‌آورد', function (): void {
    $this->get('/contact')
        ->assertOk()
        ->assertSee('hello@example.com')
        ->assertSee('ارسال پیام');
});

it('فرم تماس پیام واقعی را ذخیره می‌کند', function (): void {
    $response = $this->withSession(['contact_form_opened_at' => time() - 10])
        ->post('/contact', [
            'name' => 'کاربر تست',
            'email' => 'user@example.com',
            'purpose' => 'question',
            'message' => 'این یک پیام واقعی برای تست است.',
            'consent' => '1',
        ]);

    $response->assertRedirect(route('contact.show'));

    $this->assertDatabaseHas('contact_messages', [
        'email' => 'user@example.com',
        'is_spam' => false,
    ]);
});

it('فرم تماس ورودی نامعتبر را رد می‌کند', function (): void {
    $response = $this->withSession(['contact_form_opened_at' => time() - 10])
        ->post('/contact', [
            'name' => 'ا',                    // کوتاه‌تر از حد
            'email' => 'not-an-email',        // نامعتبر
            'message' => 'کوتاه',             // زیر ۱۰ حرف
            'consent' => '1',
        ]);

    $response->assertSessionHasErrors(['name', 'email', 'message']);
    $this->assertDatabaseCount('contact_messages', 0);
});

it('honeypot ربات را تشخیص می‌دهد و پیام را اسپم علامت می‌زند', function (): void {
    $response = $this->withSession(['contact_form_opened_at' => time() - 10])
        ->post('/contact', [
            'name' => 'ربات',
            'email' => 'bot@example.com',
            'message' => 'این پیام از یک ربات است.',
            'website' => 'http://spam.example.com', // honeypot
            'consent' => '1',
        ]);

    // پیام اسپم پذیرفته می‌شود ولی علامت‌گذاری می‌شود — به ربات
    // نمی‌گوییم «رد شد» تا الگویش را اصلاح نکند.
    $response->assertRedirect(route('contact.show'));

    $this->assertDatabaseHas('contact_messages', [
        'email' => 'bot@example.com',
        'is_spam' => true,
    ]);
});

// ── خروجی‌های SEO ──

it('sitemap فقط محتوای عمومی را فهرست می‌کند', function (): void {
    $listed = Project::query()->where('visibility', 'public')->firstOrFail();

    // پروژه‌ی unlisted — منتشر شده ولی خواسته در فهرست نباشد
    $unlisted = Project::create([
        'title' => 'پروژه بدون فهرست',
        'status' => 'completed',
        'visibility' => 'unlisted',
        'published_at' => now(),
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $content = $response->getContent();

    /*
    | URLها در sitemap به‌صورت percent-encoded هستند (رفتار درست XML).
    | پس مستقیم با slug فارسی جست‌وجو نمی‌کنیم؛ هر دو شکل را می‌سنجیم.
    */
    expect($content)->toContain(rawurlencode($listed->slug))
        ->and($content)->not->toContain(rawurlencode($unlisted->slug));
});

it('sitemap xml معتبر و ساختار درست دارد', function (): void {
    $content = $this->get('/sitemap.xml')->getContent();

    $doc = new DOMDocument;

    expect($doc->loadXML($content))->toBeTrue();

    // بررسی ساختار واقعی: باید urlset با namespace درست باشد
    $urlset = $doc->getElementsByTagName('urlset')->item(0);

    expect($urlset)->not->toBeNull()
        ->and($urlset->getAttribute('xmlns'))->toBe('http://www.sitemaps.org/schemas/sitemap/0.9')
        ->and($doc->getElementsByTagName('url')->length)->toBeGreaterThan(0);
});

it('robots.txt پنل را مسدود می‌کند', function (): void {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Disallow: /admin')
        ->assertSee('Sitemap:');
});

it('خوراک RSS معتبر است', function (): void {
    $content = $this->get('/feed')->getContent();

    $doc = new DOMDocument;
    expect($doc->loadXML($content))->toBeTrue();

    $this->get('/feed')
        ->assertOk()
        ->assertSee('یادداشت نمونه');
});

// ── جست‌وجو ──

it('جست‌وجو نتایج واقعی برمی‌گرداند', function (): void {
    $this->get('/search?q=نمونه')
        ->assertOk()
        ->assertSee('پروژه نمونه');
});

it('جست‌وجو با عبارت کوتاه نتیجه خالی می‌دهد', function (): void {
    $this->get('/search?q=ا')
        ->assertOk()
        ->assertSee('حداقل');
});

it('صفحه جست‌وجو noindex است', function (): void {
    // صفحات نتیجه‌ی جست‌وجو محتوای تکراری می‌سازند
    $this->get('/search?q=نمونه')
        ->assertOk()
        ->assertSee('noindex', false);
});

// ── حالت خالی ──

it('حالت بدون پروفایل صفحه راه‌اندازی نشان می‌دهد نه خطا', function (): void {
    Profile::query()->delete();
    Cache::flush();

    $this->get('/')
        ->assertOk()
        ->assertSee('در حال آماده‌سازی');
});

it('فهرست خالی پروژه حالت خالی نشان می‌دهد نه صفحه سفید', function (): void {
    Project::query()->forceDelete();
    Cache::flush();

    $this->get('/projects')
        ->assertOk()
        ->assertSee('هنوز پروژه‌ای ثبت نشده است');
});

// ── ماژول خاموش ──

it('ماژول خاموش مسیرش را ۴۰۴ می‌کند', function (): void {
    Module::query()->where('key', 'projects')->update(['is_enabled' => false]);
    Cache::flush();

    $this->get('/projects')->assertNotFound();
    $this->get('/')->assertOk(); // بقیه سایت سالم می‌ماند
});

it('خاموش‌کردن ماژول داده را حذف نمی‌کند', function (): void {
    $countBefore = Project::query()->count();

    Module::query()->where('key', 'projects')->update(['is_enabled' => false]);
    Cache::flush();

    $this->get('/projects')->assertNotFound();

    expect(Project::query()->count())->toBe($countBefore);
});

// ── ۴۰۴ ──

it('مسیر ناشناخته صفحه ۴۰۴ فارسی می‌دهد', function (): void {
    $this->get('/this-route-does-not-exist')
        ->assertNotFound()
        ->assertSee('صفحه پیدا نشد');
});

// ── هدرهای امنیتی ──

it('هدرهای امنیتی روی پاسخ‌ها ست می‌شوند', function (): void {
    $response = $this->get('/');

    $response->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

    expect($response->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'none'");
});

it('پنل مدیریت noindex است', function (): void {
    $response = $this->get('/admin/login');

    $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});
