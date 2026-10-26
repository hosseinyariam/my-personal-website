{{--
  بخش یادداشت‌های منتخب.

  ── نمایش مشروط تاریخ و زمان مطالعه ──
  IA:224: تاریخ و زمان مطالعه را فقط وقتی نشان بده که معنای مشخص
  و داده معتبر داشته باشند. پس:
    - تاریخ فقط اگر `published_at` (یا در نبودش، updated_at) وجود دارد
    - زمان مطالعه فقط اگر متن واقعی هست
--}}
<section class="py-14 md:py-20 section-rule" aria-labelledby="selected-notes-heading">
    <div class="container-page">
        <x-ui.section-heading
            :title="$title"
            :href="route('notes.index')"
            id="selected-notes-heading" />

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($notes as $note)
                <article class="card card-interactive h-full flex flex-col">
                    <h3 class="text-card font-semibold mb-2">
                        <a href="{{ route('notes.show', $note) }}" class="hover:text-accent transition-colors">
                            {{ $note->title }}
                        </a>
                    </h3>

                    @if ($note->excerpt)
                        <p class="text-small text-text-muted leading-relaxed flex-1">{{ $note->excerpt }}</p>
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
    </div>
</section>
