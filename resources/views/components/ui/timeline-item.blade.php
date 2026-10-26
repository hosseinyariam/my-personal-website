{{--
  یک رویداد در خط زمانی.

  ── چرا فاصله با ps-8 روی خودِ li ──
  خط عمودی در ظرف روی ۱۲٫۵px است. متن باید از خط و نشانگر فاصله بگیرد،
  پس li با padding-inline-start استاندارد (ps-8) جلو می‌رود. این ویژگی
  منطقی است و در LTR و RTL هر دو درست کار می‌کند.

  ── چرا نشانگر با start-1.5 ──
  عرض نشانگر ۱۲px است؛ با شروع از ۶px، مرکزش روی ۱۲px می‌افتد که با
  مرکز خط (۱۲٫۵px) عملاً یکی است. انحراف زیر‌پیکسلی توسط حلقه پوشیده
  می‌شود. هیچ مقدار دلبخواه یا ترنسفورم جهت‌داری لازم نیست.

  ── چرا ring به رنگ پس‌زمینه ──
  حلقه‌ی هم‌رنگ پس‌زمینه، خط را پشت نشانگر قطع می‌کند و باعث می‌شود
  نشانگر «روی» خط بنشیند، نه چسبیده به آن.

  ── چرا highlight ──
  رویدادهای شاخص (is_highlight در TimelineEvent) باید بدون اتکای صرف
  به رنگ هم متمایز باشند؛ پس نشانگر هم بزرگ‌تر و هم رنگی می‌شود.
--}}
@props([
    'date' => null,
    'title',
    'description' => null,
    'href' => null,
    'highlight' => false,
])

<li {{ $attributes->merge(['class' => 'relative ps-8']) }}>
    {{-- نشانگر --}}
    <span class="absolute start-1.5 top-2.5 rounded-full ring-4 ring-bg
                 {{ $highlight ? 'size-3.5 bg-accent' : 'size-3 bg-border-strong' }}"
          aria-hidden="true"></span>

    <div class="space-y-1">
        @if ($date)
            {{-- تاریخ از قبل قالب‌بندی‌شده می‌آید (Persian::date در کنترلر)؛
                 کامپوننت نباید منطق تاریخ داشته باشد. --}}
            <p class="text-label text-text-subtle num-fa">{{ $date }}</p>
        @endif

        <h3 class="text-card font-bold text-text">
            @if ($href)
                <a href="{{ $href }}" class="rounded-control hover:text-accent transition-colors">
                    {{ $title }}
                </a>
            @else
                {{ $title }}
            @endif
        </h3>

        @if ($description)
            <p class="text-small text-text-muted">{{ $description }}</p>
        @endif

        {{-- اسلات برای محتوای تکمیلی (فناوری‌ها، برچسب‌ها، لینک‌های فرعی). --}}
        @if (trim($slot) !== '')
            <div class="pt-1">{{ $slot }}</div>
        @endif
    </div>
</li>
