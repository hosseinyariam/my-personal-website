{{--
  کارت پروژه — جزء مشترک بین صفحه اصلی و فهرست پروژه‌ها.

  ── چرا کارت خالی از وضعیت نیست ──
  IA می‌گوید بین «ایده»، «در حال ساخت»، «فعال»، «متوقف» و «آرشیو»
  تمایز بگذار. این تمایز در کارت نمایش داده می‌شود چون مخاطب فنی
  می‌خواهد بداند با چه چیزی روبه‌روست — پروژه‌ی تمام‌شده یا کار
  در جریان.

  ── نکته‌ی دسترس‌پذیری ──
  کل کارت لینک است، ولی لینک‌های داخلی ندارد؛ اگر داشت،
  nesting لینک داخل لینک مشکل دسترس‌پذیری می‌ساخت. بقیه‌ی
  پیوندها (شرکت، محصول) در صفحه‌ی جزئیات هستند.
--}}

<article class="card card-interactive h-full flex flex-col">
    <div class="flex items-center gap-2 mb-3">
        <x-ui.badge :variant="$statusVariant ?? 'neutral'">
            {{ $project->status->label() }}
        </x-ui.badge>
    </div>

    <h3 class="text-card font-semibold mb-2">
        <a href="{{ route('projects.show', $project) }}" class="hover:text-accent transition-colors">
            {{ $project->title }}
        </a>
    </h3>

    @if ($project->summary)
        <p class="text-small text-text-muted leading-relaxed flex-1">
            {{ $project->summary }}
        </p>
    @endif

    {{-- فناوری‌ها فقط اگر واقعاً وصل شده باشند — نه فهرست ثابت --}}
    @if ($project->relationLoaded('technologies') && $project->technologies->isNotEmpty())
        <ul class="mt-4 flex flex-wrap gap-1.5" aria-label="فناوری‌های استفاده‌شده">
            @foreach ($project->technologies->take(4) as $tech)
                <li class="badge badge-neutral">{{ $tech->name }}</li>
            @endforeach
        </ul>
    @endif

    @if ($project->periodLabel ?? null)
        <p class="mt-4 text-label text-text-subtle num-fa">{{ $project->periodLabel }}</p>
    @endif
</article>
