<x-layouts.app
    :title="$post->title"
    :description="$post->meta_description ?: $post->excerpt"
    og-type="article"
    :og-image="$post->cover"
    :schema="$schema"
>
    <div class="space-y-5">
        <div data-reveal>
            <x-ui.breadcrumb :items="[
                ['label' => 'خانه', 'url' => route('home')],
                ['label' => 'وبلاگ', 'url' => route('blog.index')],
                ['label' => $post->title],
            ]" />
        </div>

        <article class="card p-7 sm:p-10" data-reveal>
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($post->tags as $tag)
                    <x-ui.chip variant="tag">{{ $tag->name }}</x-ui.chip>
                @endforeach
                <span class="text-xs text-muted">{{ fa_date($post->published_at) }}</span>
                <span class="h-1 w-1 rounded-full bg-ink-300"></span>
                <span class="flex items-center gap-1 text-xs text-muted">
                    <x-ui.icon name="clock" class="h-3.5 w-3.5" />
                    {{ fa_digits($post->reading_time) }} دقیقه
                </span>
            </div>

            <h1 class="mt-4 text-h1 text-heading">{{ $post->title }}</h1>

            @if ($post->excerpt)
                <p class="mt-5 text-sm font-medium leading-8 text-heading">{{ $post->excerpt }}</p>
            @endif

            @if ($cover = $post->coverUrl())
                <img src="{{ $cover }}" alt="{{ $post->title }}" class="mt-7 w-full rounded-2xl border border-line" loading="lazy" decoding="async">
            @endif

            <div class="post-content mt-7">
                {!! \App\Services\Markdown::render($post->content) !!}
            </div>

            <div class="divider my-8"></div>

            {{-- کارت نویسنده --}}
            <div class="flex flex-wrap items-center gap-4">
                <x-ui.avatar :name="$siteName" :src="$siteAvatar" size="md" />
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-extrabold text-heading">{{ $siteName }}</p>
                    @if ($siteHeadline)
                        <p class="mt-0.5 text-xs text-muted">{{ $siteHeadline }}</p>
                    @endif
                </div>
                <x-ui.button :href="route('feed')" variant="ghost" size="sm" icon="rss">خوراک RSS</x-ui.button>
            </div>
        </article>

        {{-- ── یادداشت‌های دیگر ─────────────────────────────── --}}
        @if ($related->isNotEmpty())
            <section data-reveal>
                <h2 class="mb-4 text-h3 text-heading">یادداشت‌های دیگر</h2>
                <div class="card divide-y divide-line p-2 sm:p-3">
                    @foreach ($related as $item)
                        <a href="{{ route('blog.show', $item->slug) }}"
                           class="group flex flex-wrap items-center gap-x-4 gap-y-2 rounded-2xl px-4 py-4 transition-colors hover:bg-canvas/70 dark:hover:bg-white/[0.04] sm:px-5">
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-heading transition-colors group-hover:text-accent-deep dark:group-hover:text-accent">{{ $item->title }}</span>
                                <span class="mt-1 block text-[11px] text-muted">{{ fa_date($item->published_at) }} · {{ fa_digits($item->reading_time) }} دقیقه</span>
                            </span>
                            <x-ui.icon name="arrow-left" class="h-4 w-4 text-muted transition-all group-hover:-translate-x-1 group-hover:text-accent" />
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
