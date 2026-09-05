@php
    $screenshots = $platform->activeScreenshots;
    $features = $platform->featureList();
    $stack = $platform->techStackList();

    // تب‌ها فقط برای بخش‌هایی که واقعاً محتوا دارند (US-M3-1)
    $tabs = collect([
        'problem' => $platform->problem ? 'مسئله' : null,
        'solution' => $platform->solution ? 'راه‌حل' : null,
        'features' => $features ? 'امکانات' : null,
    ])->filter()->all();
@endphp

<x-layouts.app
    :title="$platform->title"
    :description="$platform->tagline ?: \Illuminate\Support\Str::limit($platform->problem, 155)"
    og-type="article"
    :og-image="$platform->og_image"
    :schema="$schema"
>
    <div class="space-y-5">

        <div data-reveal>
            <x-ui.breadcrumb :items="[
                ['label' => 'خانه', 'url' => route('home')],
                ['label' => 'پلتفرم‌ها', 'url' => route('platforms.index')],
                ['label' => $platform->title],
            ]" />
        </div>

        {{-- ── سربرگ ────────────────────────────────────────── --}}
        <header class="card p-7 sm:p-8" data-reveal>
            <div class="flex flex-wrap items-start justify-between gap-5">
                <div class="flex items-center gap-4">
                    <x-ui.tile :icon="$platform->iconName()" variant="brand" size="lg" />
                    <div>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h1 class="text-h2 text-heading">{{ $platform->title }}</h1>
                            <x-ui.badge
                                :variant="$platform->status->badgeVariant()"
                                :live="$platform->status->isLive()"
                                :icon="$platform->status->icon()"
                            >{{ $platform->status->label() }}</x-ui.badge>
                        </div>
                        @if ($platform->tagline)
                            <p class="mt-1 text-sm text-body">{{ $platform->tagline }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    @if ($platform->external_url)
                        <x-ui.button
                            :href="$platform->external_url"
                            variant="primary"
                            size="md"
                            icon-after="external-link"
                            target="_blank"
                            rel="noopener noreferrer"
                            data-track="demo_click"
                            :data-track-label="$platform->slug"
                        >مشاهده‌ی دمو</x-ui.button>
                    @endif

                    @if ($platform->repo_url)
                        <x-ui.button
                            :href="$platform->repo_url"
                            variant="glass"
                            size="md"
                            icon="brand-github"
                            target="_blank"
                            rel="noopener noreferrer"
                        >کد منبع</x-ui.button>
                    @endif
                </div>
            </div>
        </header>

        <div class="grid gap-5 lg:grid-cols-[1fr_360px]">
            <div class="space-y-5">
                {{-- ── مسئله / راه‌حل / امکانات ─────────────── --}}
                @if ($tabs)
                    <x-ui.tabs :tabs="$tabs" class="card p-7 sm:p-8" data-reveal>
                        @php $first = array_key_first($tabs); @endphp

                        @if (isset($tabs['problem']))
                            <x-ui.tab-panel name="problem" :active="$first === 'problem'">
                                <p class="text-sm leading-8 text-body">{{ $platform->problem }}</p>
                            </x-ui.tab-panel>
                        @endif

                        @if (isset($tabs['solution']))
                            <x-ui.tab-panel name="solution" :active="$first === 'solution'">
                                <p class="text-sm leading-8 text-body">{{ $platform->solution }}</p>
                            </x-ui.tab-panel>
                        @endif

                        @if (isset($tabs['features']))
                            <x-ui.tab-panel name="features" :active="$first === 'features'">
                                <ul class="space-y-3.5">
                                    @foreach ($features as $feature)
                                        <li class="flex gap-3 text-sm text-body">
                                            <x-ui.icon
                                                :name="$feature['icon'] ?? 'circle-check'"
                                                class="mt-0.5 h-[1.15rem] w-[1.15rem] shrink-0 text-accent-deep dark:text-accent"
                                            />
                                            <span>
                                                <span class="font-bold text-heading">{{ $feature['title'] }}</span>
                                                @if (filled($feature['description'] ?? null))
                                                    <span class="mt-1 block leading-7">{{ $feature['description'] }}</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </x-ui.tab-panel>
                        @endif
                    </x-ui.tabs>
                @endif

                {{-- ── گالری یا ماک مرورگر (US-M3-2) ─────────── --}}
                <section data-reveal>
                    @if ($screenshots->isNotEmpty())
                        <div class="space-y-4">
                            @foreach ($screenshots as $shot)
                                <figure class="card overflow-hidden p-0">
                                    <img
                                        src="{{ $shot->url() }}"
                                        alt="{{ $shot->caption ?: "نمایی از {$platform->title}" }}"
                                        class="w-full"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                    @if ($shot->caption)
                                        <figcaption class="border-t border-line px-5 py-3 text-xs text-muted">{{ $shot->caption }}</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    @else
                        {{-- fallback قفل‌شده: ماک مرورگر برند-دار، نه فضای خالی --}}
                        <x-ui.browser-mock
                            :url="$platform->external_url ? parse_url($platform->external_url, PHP_URL_HOST) : 'hosseinyariam.ir'"
                            :title="$platform->title"
                            :icon="$platform->iconName()"
                        />
                    @endif
                </section>
            </div>

            {{-- ── مشخصات و استک ────────────────────────────── --}}
            <aside class="space-y-5">
                @if ($platform->hasSpecs())
                    <div class="card p-6" data-reveal>
                        <p class="kicker">مشخصات</p>
                        <dl class="mt-4 space-y-3.5">
                            <div class="flex items-center justify-between gap-3 text-[13px]">
                                <dt class="text-muted">وضعیت</dt>
                                <dd class="flex items-center gap-2 font-bold text-heading">
                                    @if ($platform->status->isLive())
                                        <span class="live-dot"></span>
                                    @endif
                                    {{ $platform->status->label() }}
                                </dd>
                            </div>

                            @if ($platform->version)
                                <div class="flex items-center justify-between gap-3 text-[13px]">
                                    <dt class="text-muted">نسخه</dt>
                                    <dd class="font-mono text-xs text-heading" dir="ltr">{{ $platform->version }}</dd>
                                </div>
                            @endif

                            @if ($platform->license)
                                <div class="flex items-center justify-between gap-3 text-[13px]">
                                    <dt class="text-muted">لایسنس</dt>
                                    <dd class="font-mono text-xs text-heading" dir="ltr">{{ $platform->license }}</dd>
                                </div>
                            @endif

                            @if ($platform->updated_at)
                                <div class="flex items-center justify-between gap-3 text-[13px]">
                                    <dt class="text-muted">به‌روزرسانی</dt>
                                    <dd class="font-bold text-heading">{{ fa_date($platform->updated_at, 'MMMM y') }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if ($stack)
                            <div class="divider my-5"></div>
                            <p class="mb-3 text-[11px] font-bold text-muted">استک فنی</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($stack as $tech)
                                    <x-ui.chip>{{ $tech }}</x-ui.chip>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ── CTA پایانی → فرم تماس با هدف از پیش انتخاب‌شده --}}
                <div class="card p-6" data-reveal>
                    <x-ui.tile icon="messages" variant="brand" />
                    <h2 class="mt-4 text-h3 text-heading">محصولی مشابه می‌خواهید؟</h2>
                    <p class="mt-2 text-xs leading-6 text-body">
                        اگر مسئله‌ای شبیه این دارید، بنویسید تا درباره‌ی راه‌حل و مسیر اجرا گفت‌وگو کنیم.
                    </p>
                    <x-ui.button
                        :href="route('contact.show', ['purpose' => \App\Enums\ContactPurpose::Service->value])"
                        variant="primary"
                        size="md"
                        icon="send"
                        class="mt-5 w-full"
                    >درخواست مشاوره</x-ui.button>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.app>
