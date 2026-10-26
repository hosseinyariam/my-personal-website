{{--
  بخش «تمرکز فعلی» — پاسخ به «الان روی چه چیزی کار می‌کنم؟»

  ── چرا این بخش مهم است ──
  IA:604: «Current Focus = Dynamic». این تنها بخشی است که سایت را
  از یک رزومه‌ی ایستا به یک پروفایل زنده تبدیل می‌کند. اگر به‌روز
  نباشد، سایت مرده به نظر می‌رسد.

  ── وضعیت‌های واقعی ──
  هر آیتم وضعیت خودش را نشان می‌دهد (فعال/متوقف/تمام‌شده).
  پنهان‌کردن وضعیت یعنی ادعای انجام‌شدن کاری که نشده.
--}}

<section class="py-14 md:py-20 section-rule" aria-labelledby="current-focus-heading">
    <div class="container-page">
        <x-ui.section-heading :title="$title" id="current-focus-heading" />

        <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($focuses as $focus)
                <article class="card">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <span class="badge badge-accent">{{ $focus->focus_type->label() }}</span>

                        @if ($focus->status !== \App\Enums\CurrentFocusStatus::Active)
                            <span class="badge {{ $focus->status->badgeClass() }}">
                                {{ $focus->status->label() }}
                            </span>
                        @endif
                    </div>

                    <h3 class="text-card font-semibold mb-2">
                        @if ($focus->relatedUrl())
                            <a href="{{ $focus->relatedUrl() }}" class="hover:text-accent transition-colors">
                                {{ $focus->title }}
                            </a>
                        @else
                            {{ $focus->title }}
                        @endif
                    </h3>

                    @if ($focus->description)
                        <p class="text-small text-text-muted leading-relaxed">
                            {{ $focus->description }}
                        </p>
                    @endif

                    @if ($focus->target_date)
                        <p class="mt-3 text-label text-text-subtle">
                            هدف: {{ \App\Support\Persian::date($focus->target_date, 'MMMM y') }}
                        </p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
