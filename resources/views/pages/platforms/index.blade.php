<x-layouts.app
    title="پلتفرم‌ها"
    description="پلتفرم‌ها و محصولات نرم‌افزاریِ ساخته‌شده — با وضعیت واقعی و لینک زنده."
    :schema="$schema"
>
    <header class="mb-8" data-reveal>
        <x-ui.breadcrumb :items="[
            ['label' => 'خانه', 'url' => route('home')],
            ['label' => 'پلتفرم‌ها'],
        ]" />
        <h1 class="mt-4 text-h1 text-heading">پلتفرم‌ها</h1>
        <p class="mt-3 max-w-2xl text-sm leading-7 text-body">
            محصولاتی که ساخته‌ام و اجرا می‌کنم — هرکدام با وضعیت واقعی، مسئله‌ای که حل می‌کند و لینک زنده.
        </p>
    </header>

    @if ($platforms->isEmpty())
        {{-- حالت خالی (US-M2-3) — نه صفحه‌ی خالیِ مرده --}}
        <x-ui.card data-reveal>
            <x-ui.empty-state
                icon="rocket"
                title="هنوز پلتفرمی برای نمایش نیست"
                action="گفت‌وگو کنیم"
                :href="route('contact.show')"
            >
                به‌زودی محصولاتی که رویشان کار می‌کنم همین‌جا فهرست می‌شوند. اگر ایده یا پروژه‌ای دارید، همین حالا در تماس باشید.
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <div class="grid gap-5 lg:grid-cols-12" data-reveal>
            @foreach ($platforms as $platform)
                @php
                    // کارت شاخص عریض‌تر رندر می‌شود تا گرید یکنواخت نشود (الگوی کیت)
                    $span = $platform->is_featured ? 'lg:col-span-12' : 'lg:col-span-6';
                @endphp

                <x-ui.card
                    interactive
                    :href="route('platforms.show', $platform->slug)"
                    as="article"
                    class="group block p-7 {{ $span }}"
                >
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex items-center gap-4">
                            <x-ui.tile
                                :icon="$platform->iconName()"
                                :variant="$platform->is_featured ? 'brand' : 'line'"
                            />
                            <div>
                                <h2 class="text-h3 text-heading transition-colors group-hover:text-accent-deep dark:group-hover:text-accent">{{ $platform->title }}</h2>
                                @if ($platform->tagline)
                                    <p class="mt-0.5 text-xs text-muted">{{ $platform->tagline }}</p>
                                @endif
                            </div>
                        </div>

                        <x-ui.badge
                            :variant="$platform->status->badgeVariant()"
                            :live="$platform->status->isLive()"
                            :icon="$platform->status->icon()"
                            :class="$platform->status === \App\Enums\PlatformStatus::Archived ? 'opacity-70' : ''"
                        >{{ $platform->status->label() }}</x-ui.badge>
                    </div>

                    @if ($platform->problem)
                        <p class="mt-4 text-sm leading-7 text-body">{{ \Illuminate\Support\Str::limit($platform->problem, $platform->is_featured ? 220 : 150) }}</p>
                    @endif

                    @if ($stack = $platform->techStackList())
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach (array_slice($stack, 0, $platform->is_featured ? 6 : 4) as $tech)
                                <x-ui.chip>{{ $tech }}</x-ui.chip>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-5 flex items-center justify-between gap-3">
                        <span class="link-arrow">
                            صفحه‌ی پلتفرم
                            <x-ui.icon name="arrow-left" class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        </span>
                        @if ($platform->version)
                            <span class="font-mono text-[10px] text-muted" dir="ltr">v{{ $platform->version }}</span>
                        @endif
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-layouts.app>
