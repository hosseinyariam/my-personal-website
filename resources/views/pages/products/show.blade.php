@extends('layouts.app')

{{--
  جزئیات محصول.

  ── قاعده‌ی سخت: قابلیت برنامه‌ریزی‌شده ≠ قابلیت آماده ──
  اگر محصول در وضعیت «ایده» یا «در حال توسعه» باشد (`isPreRelease`)،
  یک هشدار صریح بالای صفحه نشان داده می‌شود. معرفی قابلیتی که هنوز
  ساخته نشده به‌عنوان قابلیت آماده، دقیقاً همان «ادعای بدون عمل» است
  که مستندات برند آن را دشمن اصلی می‌داند.

  ── نمایش مشروط ──
  هر بخش فقط با داده‌ی واقعی رندر می‌شود؛ هیچ بخش خالی چاپ نمی‌شود.
--}}

@section('content')
    <div class="container-page py-10 md:py-14">

        <x-ui.breadcrumbs :items="[
            ['label' => 'خانه', 'url' => route('home')],
            ['label' => 'محصولات', 'url' => route('products.index')],
            ['label' => $product->name, 'url' => null],
        ]" />

        {{-- ── سربرگ ââ --}}}
        <header class="mt-8 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <x-ui.badge :variant="$product->status->badgeClass()">
                    {{ $product->status->label() }}
                </x-ui.badge>
            </div>

            <h1 class="text-page font-bold text-balance">{{ $product->name }}</h1>

            @if ($product->tagline)
                <p class="mt-4 text-card font-medium text-text-muted leading-relaxed">{{ $product->tagline }}</p>
            @endif

            @if ($product->summary)
                <p class="mt-4 text-body text-text-muted leading-relaxed">{{ $product->summary }}</p>
            @endif

            {{-- بازه‌ی زمانی — فقط اگر تاریخ واقعی وجود دارد --}}
            @if ($product->started_at || $product->launched_at)
                <p class="mt-5 flex items-center gap-2 text-small text-text-subtle num-fa">
                    <x-ui.icon name="calendar" class="size-4" />
                    {{ \App\Support\Persian::dateRange($product->started_at, $product->launched_at, $product->isLive()) }}
                </p>
            @endif

            {{-- لینک‌های بیرونی — با rel امنیتی --}}
            @if ($product->website_url || $product->repository_url)
                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($product->website_url)
                        <a href="{{ $product->website_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">
                            مشاهده‌ی وب‌سایت
                            <x-ui.icon name="external" class="size-4" />
                            <span class="sr-only">(در پنجره‌ی جدید)</span>
                        </a>
                    @endif

                    @if ($product->repository_url)
                        <a href="{{ $product->repository_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">
                            <x-ui.icon name="github" class="size-4" />
                            کد منبع
                            <span class="sr-only">(در پنجره‌ی جدید)</span>
                        </a>
                    @endif
                </div>
            @endif
        </header>

        {{-- ── هشدار پیش‌از‌عرضه ──
             جایگاه بالای محتوا: کاربر باید پیش از خواندن جزئیات بداند
             این قابلیت‌ها هنوز آماده نیستند. --}}
        @if ($product->isPreRelease())
            <div class="mt-8 max-w-3xl">
                <x-ui.alert variant="warning" title="این محصول هنوز در دست توسعه است">
                    این محصول در وضعیت «{{ $product->status->label() }}» قرار دارد. قابلیت‌هایی که در ادامه
                    توصیف می‌شوند برنامه‌ریزی‌شده‌اند و هنوز در دسترس نیستند.
                </x-ui.alert>
            </div>
        @endif

        {{-- ── تصویر شاخص ââ --}}}
        @if ($product->coverImage())
            <div class="mt-10">
                <x-ui.media-figure
                    :src="$product->coverImage()->url()"
                    :alt="$product->coverImage()->alt()"
                    :width="$product->coverImage()->width"
                    :height="$product->coverImage()->height"
                    :lazy="false" />
            </div>
        @endif

        {{-- ── بدنه ââ --}}}
        <div class="mt-12 grid gap-12 lg:grid-cols-3 lg:gap-16">

            <div class="lg:col-span-2 space-y-10">
                @if ($product->problem)
                    <section aria-labelledby="problem-heading">
                        <h2 id="problem-heading" class="text-section font-bold mb-4">مسئله</h2>
                        <div class="prose-fa text-text-muted">{!! nl2br(e($product->problem)) !!}</div>
                    </section>
                @endif

                @if ($product->solution)
                    <section aria-labelledby="solution-heading">
                        <h2 id="solution-heading" class="text-section font-bold mb-4">راه‌حل</h2>
                        <div class="prose-fa text-text-muted">{!! nl2br(e($product->solution)) !!}</div>
                    </section>
                @endif

                @if ($product->current_state)
                    <section aria-labelledby="state-heading">
                        <h2 id="state-heading" class="text-section font-bold mb-4">وضعیت فعلی</h2>
                        <div class="prose-fa text-text-muted">{!! nl2br(e($product->current_state)) !!}</div>
                    </section>
                @endif

                @if ($product->description)
                    <section aria-labelledby="description-heading">
                        <h2 id="description-heading" class="text-section font-bold mb-4">توضیحات بیشتر</h2>
                        <div class="prose-fa text-text-muted">{!! nl2br(e($product->description)) !!}</div>
                    </section>
                @endif
            </div>

            {{-- ── نوار کناری ââ --}}}
            <aside class="space-y-8" aria-label="اطلاعات تکمیلی">
                @if ($product->companies->isNotEmpty())
                    <section>
                        <h2 class="text-small font-semibold mb-3">شرکت‌های مرتبط</h2>
                        <ul class="space-y-2">
                            @foreach ($product->companies as $company)
                                <li>
                                    <a href="{{ route('companies.show', $company) }}"
                                       class="text-small text-accent hover:text-accent-hover transition-colors">
                                        {{ $company->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($product->notes->isNotEmpty())
                    <section>
                        <h2 class="text-small font-semibold mb-3">یادداشت‌های مرتبط</h2>
                        <ul class="space-y-3">
                            @foreach ($product->notes as $note)
                                <li>
                                    <a href="{{ route('notes.show', $note) }}"
                                       class="text-small text-accent hover:text-accent-hover transition-colors leading-relaxed">
                                        {{ $note->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </aside>
        </div>

        {{-- ── پروژه‌های مرتبط — حلقه‌ی کشف به کار فنی زیربنایی ââ --}}}
        @if ($product->projects->isNotEmpty())
            <section class="mt-16 pt-10 section-rule" aria-labelledby="related-projects-heading">
                <h2 id="related-projects-heading" class="text-section font-bold mb-6">پروژه‌های مرتبط</h2>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($product->projects as $project)
                        @php $project->loadMissing('technologies'); @endphp
                        <x-project-card :project="$project" :status-variant="$project->status->badgeClass()" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

@push('head')
    @include('partials.json-ld', ['schema' => $breadcrumb])
@endpush
