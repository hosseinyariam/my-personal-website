<?php

declare(strict_types=1);

use App\Enums\PlatformStatus;
use App\Filament\Pages\Settings as SettingsPage;
use App\Filament\Resources\PlatformResource;
use App\Filament\Resources\PostResource;
use App\Filament\Widgets\ContentReadinessWidget;
use App\Models\Platform;
use App\Models\Post;
use App\Models\User;
use App\Services\Settings;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

use function Pest\Livewire\livewire;

/** کاربر مدیرِ مجاز — همان نشانی‌ای که در env فهرست شده است. */
function admin(): User
{
    config()->set('app.admin_emails', 'admin@example.com');

    return User::factory()->create(['email' => 'admin@example.com']);
}

/* ── دسترسی ────────────────────────────────────────────── */

it('مهمان به صفحه‌ی ورود ریدایرکت می‌شود', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/platforms')->assertRedirect('/admin/login');
    $this->get('/admin/settings')->assertRedirect('/admin/login');
});

it('کاربر بیرون از فهرست مجاز، حتی با ورود موفق، به پنل راه ندارد', function () {
    // دفاع لایه‌ی دوم: احراز هویت کافی نیست، باید در فهرست env هم باشد
    config()->set('app.admin_emails', 'admin@example.com');
    $this->actingAs(User::factory()->create(['email' => 'stranger@example.com']));

    $this->get('/admin')->assertForbidden();
});

it('بدون تنظیم فهرست مجاز، هیچ‌کس وارد پنل نمی‌شود', function () {
    config()->set('app.admin_emails', '');
    $this->actingAs(User::factory()->create());

    $this->get('/admin')->assertForbidden();
});

it('کاربر واردشده به پنل دسترسی دارد', function () {
    $this->actingAs(admin());

    $this->get('/admin')->assertOk();
    $this->get('/admin/platforms')->assertOk();
    $this->get('/admin/settings')->assertOk();
    $this->get('/admin/posts')->assertOk();
    $this->get('/admin/contact-messages')->assertOk();
    $this->get('/admin/social-links')->assertOk();
});

/* ── G4 · افزودن پلتفرم بدون کدنویسی ───────────────────── */

it('پلتفرم جدید فقط از پنل ساخته می‌شود و بلافاصله عمومی می‌گردد', function () {
    // این همان مسیر فاز ۸.۲ (تسکر) است: صفر خط کد
    $this->actingAs(admin());

    livewire(PlatformResource\Pages\CreatePlatform::class)
        ->fillForm([
            'title' => 'تسکر',
            'slug' => 'tasker',
            'tagline' => 'مدیریت تسک برای تیم‌های کوچک',
            'problem' => 'هماهنگی تیم در ابزارهای پراکنده گم می‌شود.',
            'solution' => 'یک تخته‌ی مشترک با وضعیت شفاف.',
            'status' => PlatformStatus::Live->value,
            'tech_stack' => ['Laravel', 'Livewire'],
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $platform = Platform::query()->where('slug', 'tasker')->sole();
    expect($platform->title)->toBe('تسکر')
        ->and($platform->tech_stack)->toBe(['Laravel', 'Livewire']);

    // صفحه‌ی عمومی بدون هیچ تغییری در کد ساخته می‌شود
    $this->get('/platforms/tasker')->assertOk()->assertSeeText('تسکر');
    $this->get('/platforms')->assertSeeText('تسکر');
});

it('اسلاگ تکراری پذیرفته نمی‌شود', function () {
    $this->actingAs(admin());
    Platform::factory()->create(['slug' => 'vinora']);

    livewire(PlatformResource\Pages\CreatePlatform::class)
        ->fillForm(['title' => 'دیگری', 'slug' => 'vinora', 'status' => PlatformStatus::Live->value])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('اسلاگ با حروف غیرمجاز رد می‌شود', function () {
    $this->actingAs(admin());

    livewire(PlatformResource\Pages\CreatePlatform::class)
        ->fillForm(['title' => 'تست', 'slug' => 'اسلاگ فارسی', 'status' => PlatformStatus::Live->value])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('ویرایش پلتفرم کش ویترین را باطل می‌کند', function () {
    $this->actingAs(admin());
    $platform = Platform::factory()->create(['slug' => 'vinora', 'title' => 'وینورا']);

    // کش را گرم می‌کنیم
    $this->get('/platforms')->assertSeeText('وینورا');
    expect(Cache::has(CacheKeys::PLATFORMS_ACTIVE))->toBeTrue();

    // صفحه‌ی ویرایش رکورد را با کلید مسیر (slug) حل می‌کند
    livewire(PlatformResource\Pages\EditPlatform::class, ['record' => $platform->getRouteKey()])
        ->fillForm(['title' => 'وینورا نسخه‌ی دو'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Cache::has(CacheKeys::PLATFORMS_ACTIVE))->toBeFalse();
    $this->get('/platforms')->assertSeeText('وینورا نسخه‌ی دو');
});

/* ── پست: زمان مطالعه و اسلاگ خودکار ───────────────────── */

it('زمان مطالعه هنگام ذخیره‌ی پست محاسبه می‌شود', function () {
    $this->actingAs(admin());

    livewire(PostResource\Pages\CreatePost::class)
        ->fillForm([
            'title' => 'یادداشت تست',
            'slug' => 'test-note',
            'content' => str_repeat('کلمه ', 540),
            'status' => 'published',
            'published_at' => now()->subHour(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // ۵۴۰ ÷ ۱۸۰ = ۳ — بدون آنکه مدیر عددی وارد کند
    expect(Post::query()->sole()->reading_time)->toBe(3);
});

/* ── ویجت آمادگی محتوا ─────────────────────────────────── */

it('ویجت آمادگی، وضعیت واقعی دیتابیس را می‌شمارد', function () {
    $this->actingAs(admin());

    $before = livewire(ContentReadinessWidget::class)->viewData('checks');
    $platforms = collect($before)->firstWhere('key', 'platforms');
    expect($platforms['count'])->toBe(0)->and($platforms['ready'])->toBeFalse();

    Platform::factory()->create();

    $after = livewire(ContentReadinessWidget::class)->viewData('checks');
    $platforms = collect($after)->firstWhere('key', 'platforms');
    expect($platforms['count'])->toBe(1)->and($platforms['ready'])->toBeTrue();
});

it('ویجت تا تکمیل نشدن الزامات، آماده‌ی لانچ اعلام نمی‌کند', function () {
    $this->actingAs(admin());

    expect(livewire(ContentReadinessWidget::class)->viewData('isLaunchReady'))->toBeFalse();
});

/* ── تنظیمات ───────────────────────────────────────────── */

it('ذخیره‌ی تنظیمات بلافاصله روی سایت عمومی اثر می‌گذارد', function () {
    // D04: «محتوا از پنل، نه از کد» — بدون دیپلوی
    $this->actingAs(admin());

    livewire(SettingsPage::class)
        ->fillForm([
            'profile__display_name' => 'حسین یاری',
            'profile__bio_short' => 'بیوی واردشده از پنل.',
        ])
        ->call('save');

    expect(app(Settings::class)->get('profile', 'bio_short'))->toBe('بیوی واردشده از پنل.');

    $this->get('/')->assertSeeText('بیوی واردشده از پنل.');
});
