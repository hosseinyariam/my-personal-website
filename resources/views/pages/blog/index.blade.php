<x-layouts.app title="یادداشت‌های فنی" description="یادداشت‌هایی درباره‌ی معماری، محصول و توسعه‌ی نرم‌افزار.">
    <header class="mb-8" data-reveal>
        <x-ui.breadcrumb :items="[
            ['label' => 'خانه', 'url' => route('home')],
            ['label' => 'وبلاگ'],
        ]" />
        <h1 class="mt-4 text-h1 text-heading">یادداشت‌های فنی</h1>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-body">
            چیزهایی که در مسیر ساختن محصول یاد گرفته‌ام — درباره‌ی معماری، تصمیم‌های فنی و تجربه‌ی کاربری.
        </p>
    </header>

    @if ($posts->isEmpty())
        {{-- دسترسی مستقیم به URL بدون پست = حالت خالیِ طراحی‌شده (D08) --}}
        <x-ui.card data-reveal>
            <x-ui.empty-state
                icon="notes"
                title="هنوز یادداشتی منتشر نشده است"
                action="بازگشت به خانه"
                :href="route('home')"
            >
                اولین یادداشت که منتشر شود، همین‌جا فهرست می‌شود و لینک وبلاگ در ناوبری ظاهر می‌گردد.
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <div class="space-y-5">
            {{-- ── یادداشت شاخص ─────────────────────────────── --}}
            @if ($featured)
                <x-ui.card interactive :href="route('blog.show', $featured->slug)" as="article" class="group block p-7 sm:p-9" data-reveal>
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach ($featured->tags->take(1) as $tag)
                            <x-ui.chip variant="tag">{{ $tag->name }}</x-ui.chip>
                        @endforeach
                        <span class="text-xs text-muted">{{ fa_date($featured->published_at) }}</span>
                        <span class="h-1 w-1 rounded-full bg-ink-300"></span>
                        <span class="flex items-center gap-1 text-xs text-muted">
                            <x-ui.icon name="clock" class="h-3.5 w-3.5" />
                            {{ fa_digits($featured->reading_time) }} دقیقه مطالعه
                        </span>
                    </div>

                    <h2 class="mt-4 text-h2 text-heading transition-colors group-hover:text-accent-deep dark:group-hover:text-accent">{{ $featured->title }}</h2>

                    @if ($featured->excerpt)
                        <p class="mt-3 max-w-3xl text-sm leading-8 text-body">{{ $featured->excerpt }}</p>
                    @endif

                    <span class="link-arrow mt-5">
                        خواندن یادداشت
                        <x-ui.icon name="arrow-left" class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                    </span>
                </x-ui.card>
            @endif

            {{-- ── ردیف‌های فشرده ───────────────────────────── --}}
            @php $rows = $featured ? $posts->skip(1) : $posts; @endphp

            @if ($rows->isNotEmpty())
                <div class="card divide-y divide-line p-2 sm:p-3" data-reveal>
                    @foreach ($rows as $post)
                        <a href="{{ route('blog.show', $post->slug) }}"
                           class="group flex flex-wrap items-center gap-x-4 gap-y-2 rounded-2xl px-4 py-4 transition-colors hover:bg-canvas/70 dark:hover:bg-white/[0.04] sm:px-5">
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-heading transition-colors group-hover:text-accent-deep dark:group-hover:text-accent">{{ $post->title }}</span>
                                <span class="mt-1 block text-[11px] text-muted">
                                    {{ fa_date($post->published_at) }} · {{ fa_digits($post->reading_time) }} دقیقه
                                </span>
                            </span>
                            @foreach ($post->tags->take(1) as $tag)
                                <x-ui.chip>{{ $tag->name }}</x-ui.chip>
                            @endforeach
                            <x-ui.icon name="arrow-left" class="h-4 w-4 text-muted transition-all group-hover:-translate-x-1 group-hover:text-accent" />
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($posts->hasPages())
                <div class="pt-2" data-reveal>{{ $posts->links() }}</div>
            @endif
        </div>
    @endif
</x-layouts.app>
