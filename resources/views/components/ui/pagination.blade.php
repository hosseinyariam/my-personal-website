{{--
  صفحه‌بندی.

  ── چرا ویو سفارشی و نه ویو پیش‌فرض Laravel ──
  ویو پیش‌فرض صفحه‌بندی Laravel برای LTR نوشته شده: فلش‌ها و ترتیب
  «قبلی/بعدی» در RTL برعکس می‌شود و کلاس‌هایش با توکن‌های این پروژه
  هم‌خوان نیست. این کامپوننت همان کار را با ویژگی‌های منطقی و ارقام
  فارسی انجام می‌دهد.

  ── چرا پنجره‌ی شماره‌ها ──
  فهرست کامل شماره‌ها برای صفحه‌بندی طولانی، هم شلوغ است و هم تعداد
  زیادی هدف لمسی می‌سازد. یک پنجره‌ی کوچک با «…» تعادل می‌سازد.

  ── چرا hasPages ──
  اگر فقط یک صفحه وجود دارد، کنترل صفحه‌بندی معنایی ندارد و باید
  کاملاً رندر نشود (نه اینکه غیرفعال شود).
--}}
@props([
    'paginator' => null,
])

@php
    if (! $paginator || ! $paginator->hasPages()) {
        return;
    }

    $current = $paginator->currentPage();
    $last = $paginator->lastPage();

    // پنجره‌ی شماره‌ها: صفحه‌ی جاری و یک صفحه قبل/بعد، به‌علاوه‌ی
    // همیشه صفحه‌ی اول و آخر. بقیه با «…» خلاصه می‌شوند.
    $window = 1;
    $pages = [];

    for ($page = 1; $page <= $last; $page++) {
        if ($page === 1 || $page === $last || abs($page - $current) <= $window) {
            $pages[] = $page;
        } elseif (end($pages) !== '…') {
            $pages[] = '…';
        }
    }

    // دکمه‌های قبلی/بعدی با کلاس مشترک؛ هدف لمسی ۴۴px.
    $navButton = 'inline-flex h-11 min-w-11 items-center justify-center gap-1 rounded-control border border-border-strong px-3 text-small font-medium text-text transition-colors';
@endphp

<nav aria-label="صفحه‌بندی" {{ $attributes->merge(['class' => 'flex items-center justify-center gap-1']) }}>
    {{-- «قبلی»: در RTL به سمت شروع متن (راست) می‌رود، پس فلش راست. --}}
    @if ($paginator->onFirstPage())
        <span class="{{ $navButton }} opacity-50 cursor-not-allowed" aria-disabled="true">
            <x-ui.icon name="arrow-right" :size="4" />
            <span class="sr-only">صفحه‌ی قبلی</span>
        </span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}"
           rel="prev"
           aria-label="صفحه‌ی قبلی"
           class="{{ $navButton }} hover:bg-surface">
            <x-ui.icon name="arrow-right" :size="4" />
        </a>
    @endif

    {{-- شماره‌ها --}}
    @foreach ($pages as $page)
        @if ($page === '…')
            <span class="inline-flex h-11 min-w-11 items-center justify-center text-small text-text-subtle"
                  aria-hidden="true">…</span>
        @elseif ($page === $current)
            {{-- صفحه‌ی جاری: aria-label نام روشن می‌دهد و رقم از درخت
                 دسترس‌پذیری حذف می‌شود تا صفحه‌خوان «۵ صفحه‌ی جاری» را
                 دو بار نخواند. --}}
            <span class="inline-flex h-11 min-w-11 items-center justify-center rounded-control bg-accent text-small font-semibold text-white"
                  aria-current="page"
                  aria-label="صفحه‌ی جاری">
                <span class="num-fa" aria-hidden="true">{{ \App\Support\Persian::digits($page) }}</span>
            </span>
        @else
            <a href="{{ $paginator->url($page) }}"
               aria-label="صفحه‌ی {{ \App\Support\Persian::digits($page) }}"
               class="{{ $navButton }} hover:bg-surface">
                <span class="num-fa" aria-hidden="true">{{ \App\Support\Persian::digits($page) }}</span>
            </a>
        @endif
    @endforeach

    {{-- «بعدی»: فلش چپ برای پیشروی در RTL. --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}"
           rel="next"
           aria-label="صفحه‌ی بعدی"
           class="{{ $navButton }} hover:bg-surface">
            <x-ui.icon name="arrow-left" :size="4" />
        </a>
    @else
        <span class="{{ $navButton }} opacity-50 cursor-not-allowed" aria-disabled="true">
            <x-ui.icon name="arrow-left" :size="4" />
            <span class="sr-only">صفحه‌ی بعدی</span>
        </span>
    @endif
</nav>
