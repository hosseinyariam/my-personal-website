@extends('layouts.app')

{{--
  جزئیات شرکت.

  ── قاعده‌ی محتوای کافی ──
  کنترلر پیش از رسیدن به این نما بررسی می‌کند که شرکت محتوای واقعی
  دارد (`hasEnoughContent`). پس اینجا فرض می‌کنیم حداقل یکی از بخش‌ها
  پر است — با این حال هر بخش همچنان مشروط رندر می‌شود تا هیچ عنوان
  بی‌بدنه چاپ نشود.

  ── Privacy ──
  اطلاعات محرمانه‌ی شرکت هرگز وارد این صفحه نمی‌شود؛ فقط فیلدهای
  عمومی مدل و روابط `listable` استفاده می‌شوند.
--}}

@section('content')
    <div class="container-page py-10 md:py-14">

        <x-ui.breadcrumbs :items="[
            ['label' => 'خانه', 'url' => route('home')],
            ['label' => 'شرکت‌ها', 'url' => route('companies.index')],
            ['label' => $company->name, 'url' => null],
        ]" />

        {{-- ── سربرگ ââ --}}}
        <header class="mt-8 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                @if ($company->relationship_type)
                    <x-ui.badge variant="accent">
                        {{ $company->relationship_type->label() }}
                    </x-ui.badge>
                @endif

                @if ($company->isOngoing())
                    <x-ui.badge variant="success" :icon="true">فعال</x-ui.badge>
                @endif
            </div>

            <h1 class="text-page font-bold text-balance">{{ $company->name }}</h1>

            @if ($company->summary)
                <p class="mt-4 text-card text-text-muted leading-relaxed">{{ $company->summary }}</p>
            @endif

            {{-- بازه‌ی رابطه — فقط اگر تاریخ واقعی وجود دارد --}}
            @if ($company->started_at || $company->ended_at)
                <p class="mt-5 flex items-center gap-2 text-small text-text-subtle num-fa">
                    <x-ui.icon name="calendar" class="size-4" />
                    {{ \App\Support\Persian::dateRange($company->started_at, $company->ended_at, $company->isOngoing()) }}
                </p>
            @endif

            @if ($company->website_url)
                <div class="mt-6">
                    <a href="{{ $company->website_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">
                        مشاهده‌ی وب‌سایت
                        <x-ui.icon name="external" class="size-4" />
                        <span class="sr-only">(در پنجره‌ی جدید)</span>
                    </a>
                </div>
            @endif
        </header>

        {{-- ── تصویر شاخص ââ --}}}
        @if ($company->coverImage())
            <div class="mt-10">
                <x-ui.media-figure
                    :src="$company->coverImage()->url()"
                    :alt="$company->coverImage()->alt()"
                    :width="$company->coverImage()->width"
                    :height="$company->coverImage()->height"
                    :lazy="false" />
            </div>
        @endif

        {{-- ── توصیف ââ --}}}
        @if ($company->description)
            <section class="mt-12 max-w-prose" aria-labelledby="description-heading">
                <h2 id="description-heading" class="text-section font-bold mb-4">درباره‌ی شرکت</h2>
                <div class="prose-fa text-text-muted">{!! nl2br(e($company->description)) !!}</div>
            </section>
        @endif

        {{-- ── نقش من در این شرکت ââ --}}}
        @if ($company->experiences->isNotEmpty())
            <section class="mt-14" aria-labelledby="experiences-heading">
                <h2 id="experiences-heading" class="text-section font-bold mb-6">نقش من در این شرکت</h2>

                <div class="max-w-2xl">
                    <x-ui.timeline>
                        @foreach ($company->experiences as $experience)
                            <x-ui.timeline-item
                                :date="$experience->periodLabel()"
                                :title="$experience->title"
                                :description="$experience->description">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($experience->employment_type)
                                        <x-ui.badge variant="neutral">
                                            {{ $experience->employment_type->label() }}
                                        </x-ui.badge>
                                    @endif

                                    @if ($experience->location_label)
                                        <span class="text-label text-text-subtle">{{ $experience->location_label }}</span>
                                    @endif
                                </div>
                            </x-ui.timeline-item>
                        @endforeach
                    </x-ui.timeline>
                </div>
            </section>
        @endif

        {{-- ── محصولات مرتبط ââ --}}}
        @if ($company->products->isNotEmpty())
            <section class="mt-14" aria-labelledby="products-heading">
                <h2 id="products-heading" class="text-section font-bold mb-6">محصولات</h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($company->products as $product)
                        <article class="card card-interactive h-full flex flex-col">
                            <div class="mb-3">
                                <x-ui.badge :variant="$product->status->badgeClass()">
                                    {{ $product->status->label() }}
                                </x-ui.badge>
                            </div>

                            <h3 class="text-card font-semibold mb-2">
                                <a href="{{ route('products.show', $product) }}" class="hover:text-accent transition-colors">
                                    {{ $product->name }}
                                </a>
                            </h3>

                            @if ($product->summary)
                                <p class="text-small text-text-muted leading-relaxed flex-1">{{ $product->summary }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── پروژه‌های مرتبط ââ --}}}
        @if ($company->projects->isNotEmpty())
            <section class="mt-14" aria-labelledby="projects-heading">
                <h2 id="projects-heading" class="text-section font-bold mb-6">پروژه‌ها</h2>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($company->projects as $project)
                        @php $project->loadMissing('technologies'); @endphp
                        <x-project-card :project="$project" :status-variant="$project->status->badgeClass()" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── یادداشت‌های مرتبط ââ --}}}
        @if ($company->notes->isNotEmpty())
            <section class="mt-14" aria-labelledby="notes-heading">
                <h2 id="notes-heading" class="text-section font-bold mb-6">یادداشت‌های مرتبط</h2>

                <ul class="max-w-2xl space-y-3">
                    @foreach ($company->notes as $note)
                        <li>
                            <a href="{{ route('notes.show', $note) }}"
                               class="text-body text-accent hover:text-accent-hover transition-colors leading-relaxed">
                                {{ $note->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
@endsection

@push('head')
    @include('partials.json-ld', ['schema' => $breadcrumb])
@endpush
