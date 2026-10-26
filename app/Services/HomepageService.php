<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CurrentFocus;
use App\Models\HomepageSection;
use App\Models\Note;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Project;
use App\Models\TimelineEvent;
use App\Support\CacheKeys;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * سرویس ترکیب صفحه اصلی — «Living Profile».
 *
 * ── سه اصلی که این سرویس تضمین می‌کند ──
 *
 * ۱. **محتوا از CMS می‌آید، نه از کد.** ترتیب و فعال‌بودن بخش‌ها در
 *    جدول `homepage_sections` است. تغییر آن نیازمند دیپلوی نیست.
 *
 * ۲. **بخش خالی نمایش داده نمی‌شود.** UI Kit:108 صریح می‌گوید بخش
 *    غیرفعال یا خالی نباید فضای مرده بسازد. پس هر بخش قبل از رندر
 *    بررسی می‌شود که محتوای واقعی دارد یا نه.
 *
 * ۳. **یک Query برای همه.** کل ترکیب صفحه اصلی کش می‌شود. صفحه‌ی
 *    اصلی پرترافیک‌ترین صفحه است و معماری:1089 می‌گوید باید سبک
 *    بماند حتی با CMS غنی.
 * ─────────────────────────────────────────────
 */
class HomepageService
{
    /** مدت کش صفحه اصلی — کوتاه، چون محتوای پویا دارد. */
    private const CACHE_TTL_MINUTES = 30;

    /**
     * ترکیب کامل صفحه اصلی.
     *
     * @return array{profile: Profile|null, sections: Collection<int, array<string, mixed>>}
     */
    public function compose(): array
    {
        return Cache::remember(
            CacheKeys::HOMEPAGE,
            now()->addMinutes(self::CACHE_TTL_MINUTES),
            fn (): array => $this->buildComposition()
        );
    }

    /**
     * @return array{profile: Profile|null, sections: Collection<int, array<string, mixed>>}
     */
    private function buildComposition(): array
    {
        $profile = Profile::active();
        $sections = collect();

        // پروفایل نداشتن یک حالت واقعی است (سایت تازه راه افتاده).
        // در این حالت صفحه اصلی فقط Hero پیش‌فرض را نشان می‌دهد،
        // بدون ادعا یا محتوای ساختگی.
        if ($profile === null) {
            return ['profile' => null, 'sections' => $sections];
        }

        foreach ($this->visibleSections() as $section) {
            $payload = $this->buildSection($section, $profile);

            // بخشی که محتوای واقعی ندارد، اضافه نمی‌شود.
            if ($payload !== null) {
                $sections->push($payload);
            }
        }

        return ['profile' => $profile, 'sections' => $sections];
    }

    /**
     * بخش‌های فعال و مرتب، با احترام به وضعیت ماژول بالادستی.
     *
     * @return Collection<int, HomepageSection>
     */
    private function visibleSections(): Collection
    {
        return HomepageSection::query()
            ->enabled()
            ->ordered()
            ->get()
            ->filter(function (HomepageSection $section): bool {
                if (blank($section->module_key)) {
                    return true;
                }

                return \App\Models\Module::isEnabled((string) $section->module_key);
            })
            ->values();
    }

    /**
     * ساخت داده‌ی یک بخش. `null` یعنی «این بخش محتوا ندارد، رندر نشود».
     *
     * @return array<string, mixed>|null
     */
    private function buildSection(HomepageSection $section, Profile $profile): ?array
    {
        return match ($section->key) {
            'hero' => $this->heroSection($section, $profile),
            'current_focus' => $this->currentFocusSection($section),
            'featured_projects' => $this->projectsSection($section),
            'featured_products' => $this->productsSection($section),
            'selected_notes' => $this->notesSection($section),
            'journey_preview' => $this->journeySection($section),
            'biography_preview' => $this->biographySection($section, $profile),
            'how_i_think' => $this->howIThinkSection($section, $profile),
            'contact_cta' => $this->contactSection($section),
            default => null, // کلید ناشناخته — رندر نمی‌شود، نه خطا
        };
    }

    private function heroSection(HomepageSection $section, Profile $profile): ?array
    {
        // Hero همیشه نمایش داده می‌شود چون هویت خودِ سایت است.
        // ولی اگر پروفایل نام ندارد، متن جایگزین نمی‌سازیم — فقط خالی می‌ماند.
        return [
            'key' => 'hero',
            'title' => $section->label(),
            'profile' => $profile,
        ];
    }

    private function currentFocusSection(HomepageSection $section): ?array
    {
        $focuses = CurrentFocus::query()
            ->active()
            ->ordered()
            ->limit($section->limit(3))
            ->get();

        // بدون تمرکز واقعی، این بخش مخفی می‌شود. IA:604 می‌گوید این
        // بخش باید پویا باشد؛ متن ساختگی، پویایی دروغین است.
        if ($focuses->isEmpty()) {
            return null;
        }

        return [
            'key' => 'current_focus',
            'title' => $section->label(),
            'focuses' => $focuses,
        ];
    }

    private function projectsSection(HomepageSection $section): ?array
    {
        $projects = Project::query()
            ->featured()
            ->ordered()
            ->limit($section->limit(3))
            ->get();

        if ($projects->isEmpty()) {
            return null;
        }

        return [
            'key' => 'featured_projects',
            'title' => $section->label(),
            'projects' => $projects,
        ];
    }

    private function productsSection(HomepageSection $section): ?array
    {
        $products = Product::query()
            ->featured()
            ->ordered()
            ->limit($section->limit(3))
            ->get();

        if ($products->isEmpty()) {
            return null;
        }

        return [
            'key' => 'featured_products',
            'title' => $section->label(),
            'products' => $products,
        ];
    }

    private function notesSection(HomepageSection $section): ?array
    {
        $notes = Note::query()
            ->listable()
            ->ordered()
            ->limit($section->limit(3))
            ->get();

        if ($notes->isEmpty()) {
            return null;
        }

        return [
            'key' => 'selected_notes',
            'title' => $section->label(),
            'notes' => $notes,
        ];
    }

    private function journeySection(HomepageSection $section): ?array
    {
        $events = TimelineEvent::query()
            ->highlights()
            ->ordered()
            ->limit($section->limit(5))
            ->get();

        if ($events->isEmpty()) {
            return null;
        }

        return [
            'key' => 'journey_preview',
            'title' => $section->label(),
            'events' => $events,
        ];
    }

    private function biographySection(HomepageSection $section, Profile $profile): ?array
    {
        // بدون خلاصه یا فلسفه‌ی واقعی، بخش «درباره من» معنایی ندارد.
        if (blank($profile->short_bio) && blank($profile->philosophy)) {
            return null;
        }

        return [
            'key' => 'biography_preview',
            'title' => $section->label(),
            'profile' => $profile,
        ];
    }

    private function howIThinkSection(HomepageSection $section, Profile $profile): ?array
    {
        // این بخش از فلسفه‌ی ثبت‌شده می‌آید، نه از متن ثابت در Blade.
        if (blank($profile->philosophy)) {
            return null;
        }

        return [
            'key' => 'how_i_think',
            'title' => $section->label(),
            'philosophy' => $profile->philosophy,
        ];
    }

    private function contactSection(HomepageSection $section): ?array
    {
        // بخش تماس فقط اگر کانال عمومی واقعی وجود داشته باشد.
        $channels = app(SeoService::class)->publicContactChannels();

        if ($channels->isEmpty()) {
            return null;
        }

        return [
            'key' => 'contact_cta',
            'title' => $section->label(),
            'channels' => $channels,
        ];
    }

    /** باطل‌کردن کش — برای فرمان‌ها و تست. */
    public function flush(): void
    {
        Cache::forget(CacheKeys::HOMEPAGE);
    }
}
