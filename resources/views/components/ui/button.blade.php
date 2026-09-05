@props([
    'variant' => 'primary',   // primary | glass | ghost | danger
    'size' => 'md',           // sm | md | lg
    'href' => null,
    'icon' => null,
    'iconAfter' => null,
])

@php
    // نام کلاس‌ها عمداً به‌صورت رشته‌ی کامل نوشته شده‌اند، نه با درون‌یابی:
    // اسکنر Tailwind فقط رشته‌های کاملِ موجود در فایل را نگه می‌دارد.
    $variants = [
        'primary' => 'btn-primary',
        'glass' => 'btn-glass',
        'ghost' => 'btn-ghost',
        'danger' => 'btn-danger',
    ];
    $sizes = [
        'sm' => 'btn-sm',
        'md' => 'btn-md',
        'lg' => 'btn-lg',
    ];

    $classes = 'btn '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']);
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="{{ $attributes->get('type', 'button') }}" @endif
    {{ $attributes->except('type')->merge(['class' => $classes]) }}
>
    @if ($icon)
        <x-ui.icon :name="$icon" class="h-4 w-4" />
    @endif
    {{ $slot }}
    @if ($iconAfter)
        <x-ui.icon :name="$iconAfter" class="h-4 w-4" />
    @endif
</{{ $tag }}>
