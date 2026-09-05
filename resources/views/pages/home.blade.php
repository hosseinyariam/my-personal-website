<x-layouts.app :description="$siteBioShort">
    <div class="space-y-5">

        {{-- ── هیرو ─────────────────────────────────────────── --}}
        <section class="card relative overflow-hidden p-8 sm:p-10" data-reveal>
            <div class="pointer-events-none absolute -bottom-32 -start-24 h-72 w-72 rounded-full bg-brand-300/25 blur-3xl"></div>

            <div class="grid items-center gap-10 lg:grid-cols-[1fr_300px]">
                <div>
                    <div class="flex items-center gap-4">
                        <x-ui.avatar :name="$siteName" :src="$siteAvatar" size="xl" />
                        <div>
                            <h1 class="text-h2 text-heading sm:text-h1">{{ $siteName }}</h1>
                            @if ($siteHeadline || $siteLocation)
                                <p class="mt-1.5 flex flex-wrap items-center gap-2 text-sm font-bold text-accent-deep dark:text-accent">
                                    @if ($siteHeadline)
                                        <span>{{ $siteHeadline }}</span>
                                    @endif
                                    @if ($siteHeadline && $siteLocation)
                                        <span class="h-1 w-1 rounded-full bg-brand-500/60"></span>
                                    @endif
                                    @if ($siteLocation)
                                        <span class="text-xs font-medium text-muted">{{ $siteLocation }}</span>
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>

                    @if ($siteBioShort)
                        <p class="mt-6 max-w-xl text-sm leading-8 text-body">{{ $siteBioShort }}</p>
                    @endif

                    @if ($expertise->isNotEmpty())
                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach ($expertise as $item)
                                <x-ui.chip>{{ $item }}</x-ui.chip>
                            @endforeach
                        </div>
                    @endif

                    {{-- ردیف CTA — دکمه‌ی رزومه فقط با وجود فایل (US-M1-3) --}}
                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        @if ($hasResumePdf)
                            <x-ui.button :href="route('resume.download')" variant="primary" size="md" icon="download" data-track="resume_download">
                                دانلود رزومه
                            </x-ui.button>
                            <x-ui.button :href="route('contact.show')" variant="glass" size="md" icon-after="arrow-left">
                                گفت‌وگو کنیم
                            </x-ui.button>
                        @else
                            <x-ui.button :href="route('contact.show')" variant="primary" size="md" icon="send">
                                گفت‌وگو کنیم
                            </x-ui.button>
                            <x-ui.button :href="route('resume.index')" variant="glass" size="md" icon-after="arrow-left">
                                مشاهده‌ی رزومه
                            </x-ui.button>
                        @endif
                    </div>
                </div>

                {{-- پنل وضعیت --}}
                <div class="panel p-5">
                    @if ($isAvailable)
                        <x-ui.badge variant="brand" live>در دسترس برای پروژه‌ی جدید</x-ui.badge>
                    @else
                        <x-ui.badge variant="line" icon="briefcase">در حال حاضر ظرفیت تکمیل است</x-ui.badge>
                    @endif

                    <div class="mt-5 space-y-4">
                        <div class="flex items-center gap-3">
                            <x-ui.tile icon="rocket" variant="line" size="sm" />
                            <span class="text-[13px] text-body">پلتفرم فعال</span>
                            <span class="ms-auto text-sm font-black text-heading">{{ fa_digits($activePlatformCount) }}</span>
                        </div>

                        @if ($latestPost)
                            <div class="flex items-center gap-3">
                                <x-ui.tile icon="article" variant="line" size="sm" />
                                <span class="text-[13px] text-body">آخرین یادداشت</span>
                                <span class="ms-auto text-sm font-black text-heading">{{ fa_date($latestPost->published_at, 'd MMMM') }}</span>
                            </div>
                        @endif

                        @if ($siteLocation)
                            <div class="flex items-center gap-3">
                                <x-ui.tile icon="map-pin" variant="line" size="sm" />
                                <span class="text-[13px] text-body">موقعیت</span>
                                <span class="ms-auto text-sm font-black text-heading">{{ $siteLocation }}</span>
                            </div>
                        @endif

                        @if ($socialLinks->isNotEmpty())
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                @foreach ($socialLinks as $link)
                                    <x-ui.icon-button
                                        :icon="$link->icon"
                                        :label="$link->label"
                                        :href="$link->url"
                                        size="sm"
                                        target="_blank"
                                        rel="noopener noreferrer me"
                                    />
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ── بیوی بلند ────────────────────────────────────── --}}
        @if ($bioLong)
            <section class="card p-7 sm:p-9" data-reveal>
                <p class="kicker">درباره‌ی من</p>
                <div class="mt-4 max-w-3xl space-y-4 text-sm leading-8 text-body">
                    @foreach (preg_split('/\R{2,}/u', $bioLong) as $paragraph)
                        @if (trim($paragraph) !== '')
                            <p>{{ trim($paragraph) }}</p>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── تیزر پلتفرم شاخص (US-M1-2) ───────────────────── --}}
        @if ($featuredPlatform)
            <section data-reveal>
                <x-ui.card interactive :href="route('platforms.show', $featuredPlatform->slug)" as="article" class="group block p-7 sm:p-8">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex items-center gap-4">
                            <x-ui.tile :icon="$featuredPlatform->iconName()" variant="brand" />
                            <div>
                                <p class="text-[10px] font-bold text-muted">پلتفرم شاخص</p>
                                <h2 class="mt-0.5 text-h3 text-heading">{{ $featuredPlatform->title }}</h2>
                            </div>
                        </div>

                        <x-ui.badge
                            :variant="$featuredPlatform->status->badgeVariant()"
                            :live="$featuredPlatform->status->isLive()"
                            :icon="$featuredPlatform->status->icon()"
                        >{{ $featuredPlatform->status->label() }}</x-ui.badge>
                    </div>

                    @if ($featuredPlatform->tagline)
                        <p class="mt-4 text-sm leading-7 text-body">{{ $featuredPlatform->tagline }}</p>
                    @endif

                    @if ($stack = $featuredPlatform->techStackList())
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach (array_slice($stack, 0, 5) as $tech)
                                <x-ui.chip>{{ $tech }}</x-ui.chip>
                            @endforeach
                        </div>
                    @endif

                    <p class="link-arrow mt-5">
                        مشاهده‌ی کیس‌استادی
                        <x-ui.icon name="arrow-left" class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                    </p>
                </x-ui.card>
            </section>
        @endif

        {{-- ── تیزرهای ثانویه ───────────────────────────────── --}}
        <div class="grid gap-5 sm:grid-cols-2" data-reveal>
            <x-ui.card interactive :href="route('platforms.index')" class="group block p-5">
                <div class="flex items-center gap-3.5">
                    <x-ui.tile icon="layout-grid" variant="brand" size="sm" class="h-10 w-10 rounded-xl" />
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold text-muted">ویترین</p>
                        <p class="mt-0.5 truncate text-sm font-extrabold text-heading">همه‌ی پلتفرم‌ها</p>
                    </div>
                    <x-ui.icon name="arrow-left" class="h-4 w-4 text-muted transition-all group-hover:-translate-x-1 group-hover:text-accent" />
                </div>
            </x-ui.card>

            @if ($latestPost)
                <x-ui.card interactive :href="route('blog.show', $latestPost->slug)" class="group block p-5">
                    <div class="flex items-center gap-3.5">
                        <x-ui.tile icon="article" variant="brand" size="sm" class="h-10 w-10 rounded-xl" />
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold text-muted">آخرین یادداشت</p>
                            <p class="mt-0.5 truncate text-sm font-extrabold text-heading">{{ $latestPost->title }}</p>
                        </div>
                        <x-ui.icon name="arrow-left" class="h-4 w-4 text-muted transition-all group-hover:-translate-x-1 group-hover:text-accent" />
                    </div>
                </x-ui.card>
            @else
                <x-ui.card interactive :href="route('contact.show')" class="group block p-5">
                    <div class="flex items-center gap-3.5">
                        <x-ui.tile icon="send" variant="brand" size="sm" class="h-10 w-10 rounded-xl" />
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold text-muted">همکاری</p>
                            <p class="mt-0.5 truncate text-sm font-extrabold text-heading">پروژه‌ای در ذهن دارید؟</p>
                        </div>
                        <x-ui.icon name="arrow-left" class="h-4 w-4 text-muted transition-all group-hover:-translate-x-1 group-hover:text-accent" />
                    </div>
                </x-ui.card>
            @endif
        </div>
    </div>
</x-layouts.app>
