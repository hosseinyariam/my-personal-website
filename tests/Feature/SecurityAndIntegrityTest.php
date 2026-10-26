<?php

declare(strict_types=1);

use App\Enums\CurrentFocusType;
use App\Enums\PublicationStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\CurrentFocus;
use App\Models\Media;
use App\Models\Module;
use App\Models\Note;
use App\Models\Product;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Services\Ai\SsrfGuard;
use App\Services\ContactService;
use App\Services\PublishingService;
use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * ممیزی امنیت — بررسی واقعی محافظت‌ها.
 *
 * ── چه چیزی اینجا سنجیده می‌شود ──
 * دستورالعمل بخش ۱۱ فهرستی از الزامات امنیتی دارد. بعضی از آن‌ها
 * در تست‌های صفحات عمومی پوشش داده شده‌اند؛ اینجا موارد باقی‌مانده:
 * کنترل دسترسی پنل، جداسازی وضعیت دامنه از انتشار، محافظت SSRF،
 * جلوگیری از ذخیره‌ی راز در تنظیمات، و رفتار حذف.
 *
 * ── چرا تست‌ها روی رفتار واقعی‌اند نه روی کد ──
 * تست «آیا Policy وجود دارد» بی‌ارزش است. اینجا واقعاً درخواست
 * HTTP زده می‌شود و پاسخ سرور بررسی می‌شود.
 */
beforeEach(function (): void {
    seedPublicSite();
    Cache::flush();
});

// ── کنترل دسترسی پنل ──

it('کاربر بدون احراز هویت به پنل دسترسی ندارد', function (): void {
    $this->get('/admin')->assertRedirect();  // به صفحه ورود هدایت می‌شود
    $this->get('/admin/projects')->assertRedirect();
});

it('کاربر احرازشده خارج از فهرست مجاز به پنل راه نمی‌یابد', function (): void {
    config(['app.admin_emails' => 'admin@hosseinyariam.ir']);

    $stranger = User::create([
        'name' => 'غریبه',
        'email' => 'stranger@example.com',
        'password' => 'password123',
        'is_active' => true,
    ]);

    // حتی با احراز هویت کامل، چون ایمیلش در فهرست مجاز نیست
    $this->actingAs($stranger)->get('/admin')->assertForbidden();
});

it('کاربر غیرفعال حتی با ایمیل مجاز به پنل راه نمی‌یابد', function (): void {
    config(['app.admin_emails' => 'admin@hosseinyariam.ir']);

    $inactive = User::create([
        'name' => 'غیرفعال',
        'email' => 'admin@hosseinyariam.ir',
        'password' => 'password123',
        'is_active' => false,
    ]);

    expect($inactive->canAccessPanel())->toBeFalse();
    $this->actingAs($inactive)->get('/admin')->assertForbidden();
});

it('کاربر فعال با ایمیل مجاز به پنل راه می‌یابد', function (): void {
    config(['app.admin_emails' => 'admin@hosseinyariam.ir']);

    $admin = User::create([
        'name' => 'مدیر',
        'email' => 'admin@hosseinyariam.ir',
        'password' => 'password123',
        'is_active' => true,
    ]);

    expect($admin->canAccessPanel())->toBeTrue();
    $this->actingAs($admin)->get('/admin')->assertOk();
});

it('خالی بودن فهرست مجاز پنل را قفل می‌کند نه باز', function (): void {
    config(['app.admin_emails' => '']);

    $user = User::create([
        'name' => 'کاربر',
        'email' => 'anyone@example.com',
        'password' => 'password123',
        'is_active' => true,
    ]);

    // شکست امن: اگر ADMIN_EMAIL ناخواسته پاک شود، هیچ‌کس وارد نمی‌شود
    expect($user->canAccessPanel())->toBeFalse();
});

// ── جداسازی وضعیت دامنه از انتشار ──

it('وضعیت دامنه پروژه معیار انتشار نیست', function (): void {
    // پروژه‌ی «تکمیل‌شده» ولی منتشرنشده — نباید عمومی باشد
    $completed = Project::create([
        'title' => 'پروژه تکمیل‌شده منتشرنشده',
        'status' => 'completed',
        'visibility' => 'public',
        'published_at' => null,
    ]);

    expect($completed->status)->toBe(\App\Enums\ProjectStatus::Completed)
        ->and($completed->isViewable())->toBeFalse();

    // و برعکس: پروژه‌ی «در حال ساخت» که منتشر شده — باید دیده شود
    $inProgress = Project::create([
        'title' => 'پروژه در حال ساخت منتشرشده',
        'status' => 'in_progress',
        'visibility' => 'public',
        'published_at' => now(),
    ]);

    expect($inProgress->isViewable())->toBeTrue();
});

it('انتشار با تاریخ آینده محتوا را زمان‌بندی می‌کند نه منتشر', function (): void {
    $future = Project::create([
        'title' => 'پروژه زمان‌بندی‌شده',
        'status' => 'idea',
        'visibility' => 'public',
        'published_at' => now()->addDays(7),
    ]);

    expect($future->isViewable())->toBeFalse();
    $this->get(route('projects.show', $future))->assertNotFound();
});

it('محتوای unlisted با لینک دیده می‌شود ولی در فهرست نمی‌آید', function (): void {
    $unlisted = Project::create([
        'title' => 'پروژه بدون فهرست',
        'status' => 'completed',
        'visibility' => 'unlisted',
        'published_at' => now(),
    ]);

    // با لینک مستقیم: قابل مشاهده
    expect($unlisted->isViewable())->toBeTrue()
        ->and($unlisted->isListable())->toBeFalse();

    $this->get(route('projects.show', $unlisted))->assertOk();

    // ولی در فهرست نیست
    $this->get('/projects')->assertOk()->assertDontSee('پروژه بدون فهرست');
});

// ── سرویس انتشار ──

it('PublishingService تاریخ انتشار را خودکار پر می‌کند', function (): void {
    $project = Project::create([
        'title' => 'پروژه بدون تاریخ',
        'status' => 'idea',
        'visibility' => 'public',
        'published_at' => null,
    ]);

    expect($project->published_at)->toBeNull();

    app(PublishingService::class)->publish($project);

    expect($project->fresh()->published_at)->not->toBeNull()
        ->and($project->fresh()->isViewable())->toBeTrue();
});

it('لغو انتشار محتوا را از دید عمومی خارج می‌کند', function (): void {
    $note = Note::create([
        'title' => 'یادداشت منتشرشده',
        'body' => 'متن کافی برای تست معتبر.',
        'status' => 'published',
        'visibility' => 'public',
        'published_at' => now(),
    ]);

    expect($note->isViewable())->toBeTrue();

    app(PublishingService::class)->unpublish($note);

    expect($note->fresh()->isViewable())->toBeFalse();
    $this->get(route('notes.show', $note))->assertNotFound();
});

it('آرشیو محتوا را پنهان می‌کند ولی حذف نمی‌کند', function (): void {
    $company = Company::create([
        'name' => 'شرکت آرشیوی',
        'status' => 'published',
        'published_at' => now(),
    ]);

    app(PublishingService::class)->archive($company);

    // رکورد باقی است — آرشیو حذف نیست (Blueprint:91)
    expect(Company::query()->whereKey($company->getKey())->exists())->toBeTrue()
        ->and($company->fresh()->status)->toBe(PublicationStatus::Archived)
        ->and($company->fresh()->isViewable())->toBeFalse();
});

// ── محافظت SSRF ──

it('SsrfGuard آدرس‌های داخلی و خطرناک را رد می‌کند', function (string $url): void {
    expect(SsrfGuard::isSafeUrl($url))->toBeFalse();
})->with([
    'loopback' => 'https://127.0.0.1/v1',
    'srv metadata' => 'https://169.254.169.254/latest/meta-data',
    'private 10' => 'https://10.0.0.5/v1',
    'private 192' => 'https://192.168.1.1/v1',
    'private 172' => 'https://172.16.0.1/v1',
    'localhost' => 'https://localhost/v1',
    'gcp metadata' => 'https://metadata.google.internal/v1',
    'http scheme' => 'http://api.example.com/v1',
    'nonstandard port' => 'https://api.example.com:2083/v1',
    'cpanel port' => 'https://example.com:2087/v1',
]);

it('SsrfGuard آدرس عمومی https را می‌پذیرد', function (): void {
    // یک دامنه‌ی عمومی واقعی — باید معتبر باشد
    expect(SsrfGuard::isSafeUrl('https://api.openai.com/v1'))->toBeTrue();
});

it('SsrfGuard بازه‌های IP را درست تشخیص می‌دهد', function (): void {
    expect(SsrfGuard::isPublicIp('8.8.8.8'))->toBeTrue()
        ->and(SsrfGuard::isPublicIp('127.0.0.1'))->toBeFalse()
        ->and(SsrfGuard::isPublicIp('169.254.169.254'))->toBeFalse()
        ->and(SsrfGuard::isPublicIp('10.1.2.3'))->toBeFalse()
        ->and(SsrfGuard::isPublicIp('192.168.0.1'))->toBeFalse()
        ->and(SsrfGuard::isPublicIp('::1'))->toBeFalse();
});

// ── راز در تنظیمات ──

it('SettingService ذخیره‌ی کلید حساس را رد می‌کند', function (string $key): void {
    expect(fn () => app(SettingService::class)->set($key, 'some-value'))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'api_key' => 'ai.api_key',
    'password' => 'mail.password',
    'token' => 'github_token',
    'secret' => 'app.secret',
    'uppercase' => 'API_KEY',
]);

it('SettingService تنظیم عادی را می‌پذیرد و با نوع درست برمی‌گرداند', function (): void {
    $service = app(SettingService::class);

    $service->set('site.test_flag', true, \App\Enums\SettingType::Boolean, 'general', false);
    $service->set('site.test_count', 42, \App\Enums\SettingType::Integer, 'general', false);

    expect($service->get('site.test_flag'))->toBeTrue()
        ->and($service->get('site.test_count'))->toBe(42);
});

// ── حذف و یکپارچگی داده ──

it('حذف پروژه محتوای مرتبط را cascade نمی‌کند', function (): void {
    $project = Project::query()->firstOrFail();
    $product = Product::query()->firstOrFail();
    $company = Company::query()->firstOrFail();

    $project->forceDelete();

    // محصول و شرکت باید باقی بمانند — فقط پیوند pivot حذف می‌شود
    expect(Product::query()->whereKey($product->getKey())->exists())->toBeTrue()
        ->and(Company::query()->whereKey($company->getKey())->exists())->toBeTrue();
});

it('حذف شرکت سابقه کاری را پاک نمی‌کند بلکه ارجاع را خالی می‌گذارد', function (): void {
    $experience = \App\Models\Experience::query()->firstOrFail();
    $company = Company::query()->firstOrFail();

    expect($experience->company_id)->toBe($company->getKey());

    $company->forceDelete();

    $experience->refresh();
    expect(\App\Models\Experience::query()->whereKey($experience->getKey())->exists())->toBeTrue()
        ->and($experience->company_id)->toBeNull();
});

it('حذف موجودیت، ردیف SEO یتیم باقی نمی‌گذارد', function (): void {
    $project = Project::query()->firstOrFail();
    $project->seo()->create(['meta_title' => 'عنوان SEO', 'meta_description' => 'توضیح']);

    expect($project->seo()->exists())->toBeTrue();

    $project->forceDelete();

    expect(\App\Models\SeoMetadata::query()
        ->where('seoable_type', $project->getMorphClass())
        ->where('seoable_id', $project->getKey())
        ->exists())->toBeFalse();
});

// ── محافظت از اسلاگ و یگانگی ──

it('اسلاگ تکراری ساخته نمی‌شود بلکه پسوند می‌گیرد', function (): void {
    $first = Project::create([
        'title' => 'عنوان یکسان',
        'status' => 'idea',
        'visibility' => 'public',
        'published_at' => now(),
    ]);

    $second = Project::create([
        'title' => 'عنوان یکسان',
        'status' => 'idea',
        'visibility' => 'public',
        'published_at' => now(),
    ]);

    expect($first->slug)->not->toBe($second->slug)
        ->and($second->slug)->toStartWith($first->slug);
});

it('اسلاگ فارسی خوانا می‌ماند و نویسه‌گردانی نمی‌شود', function (): void {
    $project = Project::create([
        'title' => 'ساخت ویترین پلتفرم‌ها',
        'status' => 'idea',
        'visibility' => 'public',
        'published_at' => now(),
    ]);

    // باید حروف فارسی داشته باشد، نه فقط خط تیره و لاتین
    expect($project->slug)->toMatch('/[\x{0600}-\x{06FF}]/u')
        ->and($project->slug)->toContain('ویترین');
});

// ── فرم تماس: محافظت‌ها ──

it('فرم تماس سرعت غیرانسانی را اسپم تشخیص می‌دهد', function (): void {
    $service = app(ContactService::class);

    // فرم بلافاصله پر شده — ربات
    expect($service->isHumanTiming(time() - 0))->toBeFalse();

    // فرم با فاصله‌ی معقول پر شده — انسان
    expect($service->isHumanTiming(time() - 10))->toBeTrue();

    // نبود مهر زمانی — کاربر را رد نمی‌کنیم (سخت‌گیری بی‌دلیل)
    expect($service->isHumanTiming(null))->toBeTrue();
});

it('هش IP همان IP را یکسان هش می‌کند و IP خام را ذخیره نمی‌کند', function (): void {
    $service = app(ContactService::class);

    $a = $service->hashIp('203.0.113.5');
    $b = $service->hashIp('203.0.113.5');
    $c = $service->hashIp('203.0.113.6');

    expect($a)->toBe($b)          // یکسان برای throttle
        ->and($a)->not->toBe($c)  // متفاوت برای IP متفاوت
        ->and($a)->not->toContain('203')  // IP خام لو نمی‌رود
        ->and(strlen($a))->toBe(64);      // طول SHA-256
});

// ── ماژول و کش ──

it('خاموش کردن ماژول کش را باطل می‌کند', function (): void {
    expect(Module::isEnabled('projects'))->toBeTrue();

    Module::query()->where('key', 'projects')->update(['is_enabled' => false]);
    // مدل در `saved` کش را باطل می‌کند؛ اینجا update مستقیم است پس دستی باطل می‌کنیم
    Cache::forget(\App\Support\CacheKeys::moduleEnabled('projects'));

    expect(Module::isEnabled('projects'))->toBeFalse();
});

it('نبود رکورد ماژول یعنی روشن، نه خاموش', function (): void {
    // اگر seed اجرا نشده باشد، سایت نباید همه‌ی صفحاتش را ۴۰۴ کند
    Module::query()->where('key', 'notes')->delete();
    Cache::forget(\App\Support\CacheKeys::moduleEnabled('notes'));

    expect(Module::isEnabled('notes'))->toBeTrue();
    $this->get('/notes')->assertOk();
});

// ── CurrentFocus: حداکثر یک هدف ──

it('CurrentFocus بیش از یک هدف را تشخیص می‌دهد', function (): void {
    $project = Project::query()->firstOrFail();
    $product = Product::query()->firstOrFail();

    $focus = CurrentFocus::create([
        'title' => 'تمرکز دوهدفه',
        'focus_type' => CurrentFocusType::Building->value,
        'status' => 'active',
        'project_id' => $project->getKey(),
        'product_id' => $product->getKey(),
    ]);

    expect($focus->hasMultipleTargets())->toBeTrue();
});

it('CurrentFocus با یک هدف سالم است', function (): void {
    $project = Project::query()->firstOrFail();

    $focus = CurrentFocus::create([
        'title' => 'تمرکز تک‌هدفه',
        'focus_type' => CurrentFocusType::Building->value,
        'status' => 'active',
        'project_id' => $project->getKey(),
    ]);

    expect($focus->hasMultipleTargets())->toBeFalse()
        ->and($focus->relatedEntity()?->getKey())->toBe($project->getKey());
});

// ── Media detachment (بدون حذف فایل مشترک) ──

it('رسانه‌ی مشترک قابل حذف مستقیم نیست', function (): void {
    $project = Project::query()->firstOrFail();
    $note = Note::query()->firstOrFail();

    $media = Media::create([
        'disk' => 'public',
        'path' => 'media/test/shared.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 1024,
    ]);

    // وصل به دو موجودیت
    app(\App\Services\MediaService::class)->attach($media, $project);
    app(\App\Services\MediaService::class)->attach($media, $note);

    expect($media->usageCount())->toBe(2)
        ->and($media->isShared())->toBeTrue();

    // حذف بدون force باید استثنا بدهد — جلوگیری از شکستن محتوای دیگر
    expect(fn () => app(\App\Services\MediaService::class)->delete($media))
        ->toThrow(RuntimeException::class);
});

// ── مسیرهای حساس ──

it('مسیرهای پیش‌نمایش و پنل در robots مسدود هستند', function (): void {
    $content = $this->get('/robots.txt')->getContent();

    expect($content)->toContain('Disallow: /admin')
        ->and($content)->toContain('Disallow: /search');
});

it('همه مسیرهای عمومی نام‌دار و سالم هستند', function (): void {
    $expected = [
        'home', 'biography', 'projects.index', 'projects.show',
        'products.index', 'products.show', 'companies.index', 'companies.show',
        'notes.index', 'notes.show', 'journey', 'contact.show', 'contact.store',
        'search', 'feed', 'sitemap', 'robots', 'biography.resume',
    ];

    foreach ($expected as $name) {
        expect(Route::has($name))->toBeTrue("مسیر «{$name}» تعریف نشده است.");
    }
});

it('اسلاگ فارسی در URL بدون خطای سرور کار می‌کند', function (): void {
    $project = Project::query()->firstOrFail();

    // مسیر با اسلاگ فارسی percent-encoded می‌شود — باید ۲۰۰ بدهد
    $this->get('/projects/'.$project->slug)->assertOk();
});
