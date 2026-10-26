{{--
  سرصفحه‌ی صفحه.

  ── چرا eyebrow کوچک و کم‌رنگ است ──
  eyebrow زمینه‌ی دسته‌بندی می‌دهد (مثلاً «پروژه‌ها») و نباید با h1
  رقابت کند؛ بنابراین text-label و text-text-subtle است.

  ── چرا بدون text-transform: uppercase ──
  uppercase روی متن فارسی بی‌اثر است و روی برچسب‌های لاتین لاتین را
  پرسروصدا می‌کند؛ tracking کمی فاصله‌ی بصری می‌دهد بدون تغییر حروف.
--}}
@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
])

<header {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if ($eyebrow)
        <p class="text-label font-medium text-text-subtle tracking-wide">{{ $eyebrow }}</p>
    @endif

    <h1 class="text-page font-bold">{{ $title }}</h1>

    @if ($subtitle)
        <p class="text-body text-text-muted max-w-prose">{{ $subtitle }}</p>
    @endif
</header>
