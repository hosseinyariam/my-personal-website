{{--
  Header و ناوبری اصلی.

  ── اصل حاکم ──
  IA:271-275: «CMS Complexity ≠ Navigation Complexity». ناوبری عمداً
  خلوت است: شش گزینه. بخش‌هایی مثل Skills، Education، Certificates و
  Achievements در ناوبری نیستند — داخل بیوگرافی نمایش داده می‌شوند.
  دلیل: هر گزینه‌ی ناوبری، تصمیم کاربر را سنگین‌تر می‌کند.

  ── لینک‌های مشروط ──
  هر لینک فقط اگر ماژولش روشن باشد و محتوای واقعی داشته باشد ظاهر
  می‌شود. لینکی که به صفحه‌ی خالی یا ۴۰۴ می‌رود، اعتماد را کم می‌کند
  (UI Kit:88 این را صریحاً ممنوع کرده).
--}}
@php
    use App\Models\Module;
    use App\Models\Note;
    use App\Models\Product;
    use App\Models\Project;
    use App\Models\Company;

    // فقط مسیرهایی که واقعاً محتوا دارند وارد ناوبری می‌شوند.
    // کش نمی‌شود چون هر صفحه‌ی عمومی این را یک بار محاسبه می‌کند و
    // شمارش‌ها سبک‌اند؛ در صورت نیاز در CacheKeys قابل افزودن است.
    $navigation = collect([
        ['label' => 'درباره من', 'route' => 'biography', 'module' => 'biography', 'has_content' => true],
    ]);

    if (Module::isEnabled('projects') && Project::query()->listable()->exists()) {
        $navigation->push(['label' => 'پروژه‌ها', 'route' => 'projects.index', 'module' => 'projects', 'has_content' => true]);
    }

    if (Module::isEnabled('products') && Product::query()->listable()->exists()) {
        $navigation->push(['label' => 'محصولات', 'route' => 'products.index', 'module' => 'products', 'has_content' => true]);
    }

    if (Module::isEnabled('companies') && Company::query()->listable()->exists()) {
        $navigation->push(['label' => 'شرکت‌ها', 'route' => 'companies.index', 'module' => 'companies', 'has_content' => true]);
    }

    if (Module::isEnabled('notes') && Note::query()->listable()->exists()) {
        $navigation->push(['label' => 'یادداشت‌ها', 'route' => 'notes.index', 'module' => 'notes', 'has_content' => true]);
    }

    if (Module::isEnabled('timeline')) {
        $navigation->push(['label' => 'مسیر', 'route' => 'journey', 'module' => 'timeline', 'has_content' => true]);
    }

    $navigation->push(['label' => 'تماس', 'route' => 'contact.show', 'module' => null, 'has_content' => true]);
@endphp

<header class="border-b border-border bg-bg/95 backdrop-blur-sm sticky top-0 z-50">
    <div class="container-page">
        <div class="flex h-16 items-center justify-between gap-4">

            {{-- نشان --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0 rounded-control"
               aria-label="{{ config('app.name') }} — صفحه اصلی">
                <span class="grid size-9 place-items-center rounded-control bg-accent text-white text-sm font-bold"
                      aria-hidden="true">ح</span>
                <span class="font-bold text-[0.9375rem] tracking-tight">{{ config('app.name') }}</span>
            </a>

            {{-- ناوبری دسکتاپ --}}
            <nav class="hidden md:flex items-center gap-1" aria-label="ناوبری اصلی">
                @foreach ($navigation as $item)
                    @php $isActive = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])); @endphp
                    <a href="{{ route($item['route']) }}"
                       @if ($isActive) aria-current="page" @endif
                       class="rounded-control px-3 py-2 text-small font-medium transition-colors
                              {{ $isActive ? 'bg-accent-soft text-accent' : 'text-text-muted hover:text-text hover:bg-surface' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- اکشن‌های سمت چپ --}}
            <div class="flex items-center gap-1">
                {{--
                  هدف لمسی: حداقل ۴۴×۴۴ پیکسل طبق UI Kit:375.
                  `size-11` = ۴۴px است. آیکون داخلش کوچک‌تر می‌ماند
                  (`size-5`) تا ناحیه‌ی کلیک بزرگ بماند بدون اینکه
                  آیکون سنگین شود.
                --}}
                <a href="{{ route('search') }}"
                   class="grid size-11 place-items-center rounded-control text-text-muted hover:bg-surface hover:text-text transition-colors"
                   aria-label="جست‌وجو">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
                    </svg>
                </a>

                {{-- دکمه‌ی منوی موبایل — با جاوااسکریپت وانیلا، بدون کتابخانه --}}
                <button type="button"
                        class="md:hidden grid size-11 place-items-center rounded-control text-text-muted hover:bg-surface hover:text-text transition-colors"
                        data-mobile-menu-toggle
                        aria-expanded="false"
                        aria-controls="mobile-menu"
                        aria-label="باز کردن منو">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true" data-menu-icon-open>
                        <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                    <svg class="size-5 hidden" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true" data-menu-icon-close>
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- منوی موبایل — با hidden پیش‌فرض؛ جاوااسکریپت آن را باز می‌کند.
         اگر جاوااسکریپت اجرا نشود، ناوبری دسکتاپ روی موبایل پنهان است
         ولی همه‌ی مسیرها از فوتر و صفحه اصلی قابل دسترسی می‌مانند. --}}
    <nav id="mobile-menu" class="md:hidden hidden border-t border-border bg-bg" aria-label="ناوبری موبایل">
        <div class="container-page py-3">
            @foreach ($navigation as $item)
                @php $isActive = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])); @endphp
                <a href="{{ route($item['route']) }}"
                   @if ($isActive) aria-current="page" @endif
                   class="block rounded-control px-3 py-2.5 text-body font-medium
                          {{ $isActive ? 'bg-accent-soft text-accent' : 'text-text-muted hover:bg-surface hover:text-text' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </nav>
</header>
