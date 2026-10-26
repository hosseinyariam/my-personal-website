{{--
  برچسب وضعیت (Status Label).

  ── چرا نگاشت بسته ──
  مقدار $variant از داده‌ی دامنه می‌آید (مثلاً ProductStatus). نگاشت
  صریح تضمین می‌کند فقط کلاس‌های badge-* موجود در @layer components
  استفاده شوند و مقدار نامعتبر بی‌صدا یک برچسب بی‌رنگ نسازد.

  ── چرا live-dot ──
  رنگ نقطه از currentColor می‌آید، پس خودکار با رنگ متن همان واریانت
  هم‌خوان می‌شود؛ نیاز به کلاس رنگ جداگانه نیست.
--}}
@props([
    'variant' => 'neutral',
    'icon' => false,
])

@php
    $variantClass = match ($variant) {
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger' => 'badge-danger',
        'info' => 'badge-info',
        'accent' => 'badge-accent',
        default => 'badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge '.$variantClass]) }}>
    @if ($icon)
        {{-- display:flex لازم است تا ::before نقطه به یک آیتم فلکس تبدیل و
             width/height آن واقعاً اعمال شود (روی عنصر inline اعمال نمی‌شد). --}}
        <span class="live-dot flex shrink-0" aria-hidden="true"></span>
    @endif

    {{ $slot }}
</span>
