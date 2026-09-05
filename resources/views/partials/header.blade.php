@php
    // ناوبری کاملاً از مسیرهای نام‌دار — وبلاگ فقط با وجود پست منتشرشده (D08)
    $navigation = collect([
        ['label' => 'خانه', 'route' => 'home'],
        ['label' => 'پلتفرم‌ها', 'route' => 'platforms.index'],
        ['label' => 'رزومه', 'route' => 'resume.index'],
        ['label' => 'وبلاگ', 'route' => 'blog.index', 'when' => $blogEnabled ?? false],
    ])->reject(fn ($item) => array_key_exists('when', $item) && ! $item['when']);
@endphp

<header class="fixed inset-x-0 top-4 z-40 px-4">
    <div class="mx-auto max-w-6xl">
        <nav class="glass flex items-center gap-3 rounded-2xl px-3 py-2" aria-label="ناوبری اصلی">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500/60">
                <x-ui.avatar :name="$siteName ?? 'حسین یاری'" :src="$siteAvatar ?? null" size="sm" />
                <span class="hidden leading-tight sm:block">
                    <span class="block text-sm font-extrabold text-heading">{{ $siteName ?? 'حسین یاری' }}</span>
                    @if (! empty($siteHeadline))
                        <span class="block text-[10px] text-muted">{{ $siteHeadline }}</span>
                    @endif
                </span>
            </a>

            <div class="mx-auto hidden items-center gap-1 md:flex">
                @foreach ($navigation as $item)
                    <a href="{{ route($item['route']) }}"
                       @class(['nav-link', 'is-active' => request()->routeIs(str_replace('.index', '.*', $item['route']))])
                       @if (request()->routeIs(str_replace('.index', '.*', $item['route']))) aria-current="page" @endif
                    >{{ $item['label'] }}</a>
                @endforeach
            </div>

            <div class="ms-auto flex items-center gap-2">
                <button type="button" data-theme-toggle class="icon-btn" aria-label="تغییر تم" aria-pressed="false">
                    <svg class="h-[1.15em] w-[1.15em]" data-theme-icon aria-hidden="true"><use href="{{ asset('icons.svg') }}#i-moon"></use></svg>
                </button>

                <x-ui.button :href="route('contact.show')" variant="primary" size="sm" class="hidden md:inline-flex">تماس با من</x-ui.button>

                <button type="button" id="menuBtn" class="icon-btn md:hidden" aria-label="منو" aria-expanded="false" aria-controls="mobileMenu">
                    <x-ui.icon name="menu-2" class="h-[1.15em] w-[1.15em]" />
                </button>
            </div>
        </nav>

        <div id="mobileMenu" class="glass mt-2 hidden flex-col gap-1 rounded-2xl p-3 md:hidden">
            @foreach ($navigation as $item)
                <a href="{{ route($item['route']) }}" @class(['nav-link', 'is-active' => request()->routeIs(str_replace('.index', '.*', $item['route']))])>{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('contact.show') }}" class="nav-link font-bold text-accent-deep dark:text-accent">تماس با من</a>
        </div>
    </div>
</header>
