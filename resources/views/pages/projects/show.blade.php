@extends('layouts.app')

{{--
  جزئیات پروژه — پرونده‌ی کامل یک کار.

  ── ساختار از IA:129-142 ──
  مسئله، زمینه، نقش، مشارکت، راه‌حل، نتیجه، آموخته‌ها. ترتیب عمدی است:
  از «چرا» به «چه» به «چطور» به «چه شد» — همان زنجیره‌ای که مخاطب
  فنی انتظار دارد.

  ── اصل صداقت در نمایش ──
  هر بخش فقط اگر داده دارد رندر می‌شود. هیچ متن جایگزینِ «در دست
  تکمیل» نوشته نمی‌شود — بخش خالی، حذف می‌شود. دلیل: ادعای ناقص
  بدتر از سکوت است.

  ── آموخته‌ها حتی در پروژه‌ی ناموفق ──
  IA:144-152 «Failed / Learned» را وضعیت معتبر می‌داند. بخش
  آموخته‌ها با تأکید بصری نمایش داده می‌شود، نه در حاشیه.
--}}

@php
    use App\Enums\ProjectStatus;
@endphp

@section('content')
    <div class="container-page py-10 md:py-14">

        <x-ui.breadcrumbs :items="[
            ['label' => 'خانه', 'url' => route('home')],
            ['label' => 'پروژه‌ها', 'url' => route('projects.index')],
            ['label' => $project->title, 'url' => null],
        ]" />

        {{-- ── سربرگ ── --}}
        <header class="mt-8 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <x-ui.badge :variant="$project->status->badgeClass()">
                    {{ $project->status->label() }}
                </x-ui.badge>

                @if ($project->role)
                    <span class="text-label text-text-subtle">{{ $project->role }}</span>
                @endif
            </div>

            <h1 class="text-page font-bold text-balance">{{ $project->title }}</h1>

            @if ($project->summary)
                <p class="mt-4 text-card text-text-muted leading-relaxed">{{ $project->summary }}</p>
            @endif

            {{-- بازه‌ی زمانی — فقط اگر تاریخ واقعی وجود دارد --}}
            @if ($project->started_at || $project->completed_at)
                <p class="mt-5 flex items-center gap-2 text-small text-text-subtle num-fa">
                    <x-ui.icon name="calendar" class="size-4" />
                    {{ \App\Support\Persian::dateRange($project->started_at, $project->completed_at, $project->isActive()) }}
                </p>
            @endif

            {{-- لینک‌های بیرونی — با rel امنیتی --}}
            @if ($project->live_url || $project->repository_url)
                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($project->live_url)
                        <a href="{{ $project->live_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">
                            مشاهده‌ی زنده
                            <x-ui.icon name="external" class="size-4" />
                        </a>
                    @endif

                    @if ($project->repository_url)
                        <a href="{{ $project->repository_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">
                            <x-ui.icon name="github" class="size-4" />
                            کد منبع
                        </a>
                    @endif
                </div>
            @endif
        </header>

        {{-- ── تصویر شاخص ── --}}
        @if ($project->coverImage())
            <div class="mt-10">
                <x-ui.media-figure
                    :src="$project->coverImage()->url()"
                    :alt="$project->coverImage()->alt()"
                    :width="$project->coverImage()->width"
                    :height="$project->coverImage()->height"
                    :lazy="false" />
            </div>
        @endif

        {{-- ── بدنه ── --}}
        <div class="mt-12 grid gap-12 lg:grid-cols-3 lg:gap-16">

            <div class="lg:col-span-2 space-y-10">

                @if ($project->problem)
                    <section aria-labelledby="problem-heading">
                        <h2 id="problem-heading" class="text-section font-bold mb-4">مسئله</h2>
                        <div class="prose-fa text-text-muted">{!! nl2br(e($project->problem)) !!}</div>
                    </section>
                @endif

                @if ($project->context)
                    <section aria-labelledby="context-heading">
                        <h2 id="context-heading" class="text-section font-bold mb-4">زمینه</h2>
                        <div class="prose-fa text-text-muted">{!! nl2br(e($project->context)) !!}</div>
                    </section>
                @endif

                @if ($project->contribution || $project->description)
                    <section aria-labelledby="contribution-heading">
                        <h2 id="contribution-heading" class="text-section font-bold mb-4">رویکرد و اجرا</h2>
                        <div class="prose-fa text-text-muted">
                            {!! nl2br(e($project->contribution ?: $project->description)) !!}
                        </div>
                    </section>
                @endif

                @if ($project->outcome)
                    <section aria-labelledby="outcome-heading">
                        <h2 id="outcome-heading" class="text-section font-bold mb-4">نتیجه</h2>
                        <div class="prose-fa text-text-muted">{!! nl2br(e($project->outcome)) !!}</div>
                    </section>
                @endif

                {{-- ── آموخته‌ها: با تأکید بصری ──
                     این بخش هسته‌ی هویت «سازنده‌ی قابل اعتماد» است.
                     پروژه‌ای که درس واقعی داده، شاهد قوی‌تری از پروژه‌ی
                     موفق بی‌جزئیات است. پس در یک کارت برجسته می‌آید. --}}
                @if ($project->lessons_learned)
                    <section aria-labelledby="lessons-heading">
                        <div class="card bg-accent-soft border-0">
                            <h2 id="lessons-heading" class="text-section font-bold mb-4">آموخته‌ها</h2>
                            <div class="prose-fa text-text">
                                {!! nl2br(e($project->lessons_learned)) !!}
                            </div>
                        </div>
                    </section>
                @endif

                {{-- گالری تصاویر — بدون تصویر شاخص --}}
                @if ($project->gallery()->isNotEmpty())
                    <section aria-labelledby="gallery-heading">
                        <h2 id="gallery-heading" class="text-section font-bold mb-4">تصاویر</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ($project->gallery() as $image)
                                <x-ui.media-figure
                                    :src="$image->url()"
                                    :alt="$image->alt()"
                                    :caption="$image->caption"
                                    :width="$image->width"
                                    :height="$image->height" />
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            {{-- ── نوار کناری ── --}}
            <aside class="space-y-8" aria-label="اطلاعات تکمیلی">

                @if ($project->technologies->isNotEmpty())
                    <section>
                        <h2 class="text-small font-semibold mb-3">فناوری‌ها</h2>
                        <ul class="flex flex-wrap gap-1.5">
                            @foreach ($project->technologies as $tech)
                                <li class="badge badge-neutral">{{ $tech->name }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($project->skills->isNotEmpty())
                    <section>
                        <h2 class="text-small font-semibold mb-3">مهارت‌های به‌کاررفته</h2>
                        <ul class="flex flex-wrap gap-1.5">
                            @foreach ($project->skills as $skill)
                                <li class="badge badge-accent">{{ $skill->name }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($project->companies->isNotEmpty())
                    <section>
                        <h2 class="text-small font-semibold mb-3">شرکت‌های مرتبط</h2>
                        <ul class="space-y-2">
                            @foreach ($project->companies as $company)
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

                @if ($project->products->isNotEmpty())
                    <section>
                        <h2 class="text-small font-semibold mb-3">محصولات مرتبط</h2>
                        <ul class="space-y-2">
                            @foreach ($project->products as $product)
                                <li>
                                    <a href="{{ route('products.show', $product) }}"
                                       class="text-small text-accent hover:text-accent-hover transition-colors">
                                        {{ $product->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- حلقه‌ی کشف: از پروژه به یادداشت مرتبط --}}
                @if ($project->notes->isNotEmpty())
                    <section>
                        <h2 class="text-small font-semibold mb-3">یادداشت‌های مرتبط</h2>
                        <ul class="space-y-3">
                            @foreach ($project->notes as $note)
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

        {{-- ── دعوت به ارتباط ── --}}
        @if (\App\Models\Module::isEnabled('biography'))
            <div class="mt-16 pt-8 section-rule">
                <a href="{{ route('biography') }}" class="text-small text-accent hover:text-accent-hover transition-colors inline-flex items-center gap-2">
                    <x-ui.icon name="arrow-right" class="size-4" />
                    بیشتر درباره‌ی من و رویکردم
                </a>
            </div>
        @endif
    </div>
@endsection

@push('head')
    @include('partials.json-ld', ['schema' => $breadcrumb])
@endpush
