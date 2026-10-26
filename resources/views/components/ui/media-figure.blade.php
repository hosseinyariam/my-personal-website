{{--
  تصویر با توضیح (Media Figure).

  ── چرا width/height اجباری است ──
  مرورگر بدون ابعاد، فضایی برای تصویر رزرو نمی‌کند؛ هنگام بارگذاری
  کل چیدمان می‌پرد (CLS). این پروژه در Core Web Vitals به CLS حساس
  است، پس width و height همیشه خروجی می‌شوند.

  ── چرا decoding="async" ──
  رمزگشایی تصویر نباید رشته‌ی اصلی رندر را بلاک کند. lazy به‌طور
  پیش‌فرض روشن است، اما برای تصویر بالای صفحه (LCP) باید خاموش شود —
  به همین دلیل یک پرچم جدا دارد.

  ── چرا aspect-ratio ──
  اگر ابعاد واقعی نادرست باشند، aspect-ratio تضمین می‌کند قاب هم‌اندازه
  بماند و با object-cover تصویر بدون اعوجاج برش بخورد.
--}}
@props([
    'src',
    'alt',
    'caption' => null,
    'width' => null,
    'height' => null,
    'lazy' => true,
])

<figure {{ $attributes->merge(['class' => 'space-y-2']) }}>
    <img src="{{ $src }}"
         alt="{{ $alt }}"
         @if ($width) width="{{ $width }}" @endif
         @if ($height) height="{{ $height }}" @endif
         @if ($width && $height) style="aspect-ratio: {{ $width }} / {{ $height }};" @endif
         loading="{{ $lazy ? 'lazy' : 'eager' }}"
         decoding="async"
         class="w-full h-auto rounded-control border border-border object-cover bg-surface">

    @if ($caption)
        <figcaption class="text-label text-text-subtle">{{ $caption }}</figcaption>
    @endif
</figure>
