@extends('layouts.app')

{{--
  فهرست یادداشت‌ها.

  ── عنوان پویا ──
  اگر فیلتر دسته یا برچسب فعال باشد، نام آن در عنوان می‌آید؛ وگرنه
  عنوان عمومی «یادداشت‌ها». این به کاربر می‌گوید کجا ایستاده است.

  ── نمایش مشروط تاریخ ──
  IA:224: تاریخ را فقط وقتی نشان بده که داده‌ی معتبر دارد.
  `displayDate()` ممکن است null باشد؛ پیش از رندر بررسی می‌شود.
--}}

@section('content')
    <div class="container-page py-12 md:py-16">

        <x-ui.page-heading
            :title="$activeCategory?->name ?? $activeTag?->name ?? 'یادداشت‌ها'"
            subtitle="یادداشت‌هایی درباره ساختن محصول، حل مسئله، مهندسی نرم‌افزار و درس‌های مسیر." />

        {{-- ── نوار فیلتر ──
             فقط اگر دسته یا برچسبی با یادداشت منتشرشده وجود دارد رندر
             می‌شود؛ وگرنه فیلتر بی‌محتوا فقط شلوغی می‌سازد. --}}
        @if ($categories->isNotEmpty() || $tags->isNotEmpty())
            <div class="mt-8 space-y-4">
                @if ($categories->isNotEmpty())
                    <nav aria-label="فیلتر بر اساس دسته">
                        <ul class="flex flex-wrap gap-2">
                            {{-- «همه» همیشه اول می‌آید و در حالت بی‌فیلتر فعال است. --}}
                            <li>
                                <a href="{{ route('notes.index') }}"
                                   @if ($activeCategory === null && $activeTag === null) aria-current="page" @endif
                                   class="badge {{ $activeCategory === null && $activeTag === null ? 'badge-accent' : 'badge-neutral' }}">
                                    همه
                                </a>
                            </li>

                            @foreach ($categories as $category)
                                @php $isActive = $activeCategory?->getKey() === $category->getKey(); @endphp
                                <li>
                                    <a href="{{ route('notes.index', ['category' => $category->slug]) }}"
                                       @if ($isActive) aria-current="page" @endif
                                       class="badge {{ $isActive ? 'badge-accent' : 'badge-neutral' }}">
                                        {{ $category->name }}
                                        <span class="num-fa" aria-hidden="true">{{ \App\Support\Persian::digits($category->notes_count) }}</span>
                                        <span class="sr-only">یادداشت</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif

                @if ($tags->isNotEmpty())
                    <nav aria-label="فیلتر بر اساس برچسب">
                        <ul class="flex flex-wrap gap-2">
                            @foreach ($tags as $tag)
                                @php $isActive = $activeTag?->getKey() === $tag->getKey(); @endphp
                                <li>
                                    <a href="{{ route('notes.index', ['tag' => $tag->slug]) }}"
                                       @if ($isActive) aria-current="page" @endif
                                       class="badge {{ $isActive ? 'badge-info' : 'badge-neutral' }}">
                                        {{ $tag->name }}
                                        <span class="num-fa" aria-hidden="true">{{ \App\Support\Persian::digits($tag->notes_count) }}</span>
                                        <span class="sr-only">یادداشت</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif
            </div>
        @endif

        {{-- ── فهرست یادداشت‌ها ââ --}}}
        @if ($notes->isEmpty())
            <div class="mt-12">
                <x-ui.empty-state
                    icon="note"
                    title="یادداشتی پیدا نشد"
                    description="با این فیلتر یادداشتی ثبت نشده است. می‌توانید فیلتر را بردارید یا بعداً سر بزنید.">
                    <a href="{{ route('notes.index') }}" class="btn btn-secondary">نمایش همه‌ی یادداشت‌ها</a>
                </x-ui.empty-state>
            </div>
        @else
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($notes as $note)
                    <article class="card card-interactive h-full flex flex-col">
                        <div class="mb-3">
                            <x-ui.badge variant="neutral">{{ $note->type->label() }}</x-ui.badge>
                        </div>

                        <h2 class="text-card font-semibold mb-2">
                            <a href="{{ route('notes.show', $note) }}" class="hover:text-accent transition-colors">
                                {{ $note->title }}
                            </a>
                        </h2>

                        @if ($note->excerpt)
                            <p class="text-small text-text-muted leading-relaxed flex-1">{{ $note->excerpt }}</p>
                        @endif

                        {{-- دسته‌های یادداشت — چیپ‌های کوچک غیرلینک تا کلیک کارت را نشکنند. --}}
                        @if ($note->categories->isNotEmpty())
                            <ul class="mt-4 flex flex-wrap gap-1.5" aria-label="دسته‌های یادداشت">
                                @foreach ($note->categories as $category)
                                    <li class="badge badge-neutral">{{ $category->name }}</li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-4 flex flex-wrap items-center gap-3 text-label text-text-subtle">
                            @if ($note->displayDate())
                                <span class="num-fa">{{ \App\Support\Persian::date($note->displayDate(), 'd MMMM y') }}</span>
                            @endif

                            @if (filled($note->body))
                                <span class="num-fa">{{ $note->readingTimeLabel() }}</span>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-12">
                <x-ui.pagination :paginator="$notes" />
            </div>
        @endif
    </div>
@endsection
