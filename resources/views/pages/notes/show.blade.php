@extends('layouts.app')

{{--
  جزئیات یادداشت.

  ── چرا بدنه با {!! !!} رندر می‌شود ──
  `body` یک HTML تمیزشده است که در زمان نوشتن و ذخیره در پنل مدیریت
  پاک‌سازی (sanitize) می‌شود. یعنی ورودی خام کاربر نیست و اینجا
  دوباره escape نمی‌شود — چون آن‌وقت تگ‌های مجاز (پاراگراف، فهرست،
  کد) به‌صورت متن نمایش داده می‌شدند. تصمیم آگاهانه: پاک‌سازی در
  زمان نوشتن، نه زمان خواندن.

  ── حلقه‌ی کشف ──
  نوار کناری کاربر را از یادداشت به پروژه، محصول و شرکت مرتبط می‌برد
  (IA:341-355). همه‌ی این‌ها از روابط `listable` می‌آیند.
--}}

@section('content')
    <div class="container-page py-10 md:py-14">

        <x-ui.breadcrumbs :items="[
            ['label' => 'خانه', 'url' => route('home')],
            ['label' => 'یادداشت‌ها', 'url' => route('notes.index')],
            ['label' => $note->title, 'url' => null],
        ]" />

        {{-- ── سربرگ ââ --}}}
        <header class="mt-8 container-prose">
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <x-ui.badge variant="neutral">{{ $note->type->label() }}</x-ui.badge>
            </div>

            <h1 class="text-page font-bold text-balance">{{ $note->title }}</h1>

            @if ($note->excerpt)
                <p class="mt-4 text-card text-text-muted leading-relaxed">{{ $note->excerpt }}</p>
            @endif

            {{-- تاریخ و زمان مطالعه — هر کدام فقط با داده‌ی معتبر --}}
            @if ($note->displayDate() || filled($note->body))
                <div class="mt-5 flex flex-wrap items-center gap-4 text-small text-text-subtle">
                    @if ($note->displayDate())
                        <span class="num-fa inline-flex items-center gap-2">
                            <x-ui.icon name="calendar" class="size-4" />
                            {{ \App\Support\Persian::date($note->displayDate(), 'd MMMM y') }}
                        </span>
                    @endif

                    @if (filled($note->body))
                        <span class="num-fa">{{ $note->readingTimeLabel() }}</span>
                    @endif
                </div>
            @endif
        </header>

        {{-- ── تصویر شاخص ââ --}}}
        @if ($note->coverImage())
            <div class="mt-10">
                <x-ui.media-figure
                    :src="$note->coverImage()->url()"
                    :alt="$note->coverImage()->alt()"
                    :width="$note->coverImage()->width"
                    :height="$note->coverImage()->height"
                    :lazy="false" />
            </div>
        @endif

        {{-- ── بدنه ââ --}}}
        <div class="mt-12 grid gap-12 lg:grid-cols-3 lg:gap-16">

            <article class="prose-fa text-text lg:col-span-2">
                {{-- HTML تمیزشده‌ی ذخیره‌شده در پنل؛ پاک‌سازی در زمان نوشتن
                     انجام می‌شود، پس اینجا دوباره escape نمی‌شود. --}}
                {!! $note->body !!}
            </article>

            {{-- ── نوار کناری: حلقه‌ی کشف ââ --}}}
            <aside class="space-y-8" aria-label="محتوای مرتبط">
                @if ($note->projects->isNotEmpty())
                    <section>
                        <h2 class="text-small font-semibold mb-3">پروژه‌های مرتبط</h2>
                        <ul class="space-y-2">
                            @foreach ($note->projects as $project)
                                <li>
                                    <a href="{{ route('projects.show', $project) }}"
                                       class="text-small text-accent hover:text-accent-hover transition-colors">
                                        {{ $project->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($note->products->isNotEmpty())
                    <section>
                        <h2 class="text-small font-semibold mb-3">محصولات مرتبط</h2>
                        <ul class="space-y-2">
                            @foreach ($note->products as $product)
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

                @if ($note->companies->isNotEmpty())
                    <section>
                        <h2 class="text-small font-semibold mb-3">شرکت‌های مرتبط</h2>
                        <ul class="space-y-2">
                            @foreach ($note->companies as $company)
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

                @if ($note->tags->isNotEmpty())
                    <section>
                        <h2 class="text-small font-semibold mb-3">برچسب‌ها</h2>
                        <ul class="flex flex-wrap gap-1.5">
                            @foreach ($note->tags as $tag)
                                <li>
                                    <a href="{{ route('notes.index', ['tag' => $tag->slug]) }}" class="badge badge-neutral">
                                        {{ $tag->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </aside>
        </div>

        {{-- ── یادداشت‌های مرتبط ââ --}}}
        @if ($relatedNotes->isNotEmpty())
            <section class="mt-16 pt-10 section-rule" aria-labelledby="related-notes-heading">
                <h2 id="related-notes-heading" class="text-section font-bold mb-6">یادداشت‌های مرتبط</h2>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($relatedNotes as $related)
                        <article class="card card-interactive h-full flex flex-col">
                            <div class="mb-3">
                                <x-ui.badge variant="neutral">{{ $related->type->label() }}</x-ui.badge>
                            </div>

                            <h3 class="text-card font-semibold mb-2">
                                <a href="{{ route('notes.show', $related) }}" class="hover:text-accent transition-colors">
                                    {{ $related->title }}
                                </a>
                            </h3>

                            @if ($related->excerpt)
                                <p class="text-small text-text-muted leading-relaxed flex-1">{{ $related->excerpt }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

@push('head')
    @include('partials.json-ld', ['schema' => $breadcrumb])
@endpush
