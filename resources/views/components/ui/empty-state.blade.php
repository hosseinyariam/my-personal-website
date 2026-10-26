{{--
  حالت خالی (Empty State).

  ── چرا اجباری است ──
  UI Kit §10 «Empty» و معیار پذیرش §15: «صفحات اصلی و حالت‌های خالی
  تعریف شده‌اند» و «محتوای غایب به بخش خالی یا ادعای ساختگی منجر
  نمی‌شود». هر فهرست بدون داده باید این کامپوننت را رندر کند، نه یک
  ناحیه‌ی سفید.

  ── چرا icon نام رشته‌ای است ──
  کامپوننت نمی‌داند هر صفحه چه آیکونی می‌خواهد؛ اما باید همیشه یک آیکون
  تزئینی نشان دهد. پیش‌فرض inbox است و آیکون همیشه aria-hidden است.
--}}
@props([
    'title',
    'description' => null,
    'icon' => 'inbox',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 py-12 px-6 text-center']) }}>
    {{-- اندازه‌ی بزرگ‌تر و رنگ کم‌رنگ: حضور بصری دارد ولی با متن رقابت نمی‌کند. --}}
    <span class="grid size-12 place-items-center rounded-card bg-surface text-text-subtle" aria-hidden="true">
        <x-ui.icon :name="$icon" :size="6" />
    </span>

    <h3 class="text-card font-bold text-text">{{ $title }}</h3>

    @if ($description)
        <p class="text-small text-text-muted max-w-sm">{{ $description }}</p>
    @endif

    {{-- اسلات اختیاری برای دکمه‌ی اقدام (CTA). اگر نداشته باشد، فقط
         راهنمای متنی می‌ماند که برای صفحه‌خوان هم کافی است. --}}
    @if (trim($slot) !== '')
        <div class="mt-2">{{ $slot }}</div>
    @endif
</div>
