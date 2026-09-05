@props([
    'icon' => null,
    'variant' => 'brand',   // brand | line | ink | deep
    'size' => 'md',         // sm | md | lg | xl
])

@php
    $variants = [
        'brand' => 'tile-brand',
        'line' => 'tile-line',
        'ink' => 'tile-ink',
        'deep' => 'tile-deep',
    ];
    $sizes = [
        'sm' => 'h-9 w-9 rounded-xl text-sm',
        'md' => '',                              // اندازه‌ی پایه‌ی .icon-tile
        'lg' => 'h-14 w-14 text-2xl',
        'xl' => 'h-16 w-16 text-3xl',
    ];
    $glyphs = ['sm' => 'h-4 w-4', 'md' => 'h-5 w-5', 'lg' => 'h-7 w-7', 'xl' => 'h-8 w-8'];

    $classes = trim('icon-tile '.($variants[$variant] ?? $variants['brand']).' '.($sizes[$size] ?? ''));
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" :class="$glyphs[$size] ?? $glyphs['md']" />
    @else
        {{ $slot }}
    @endif
</span>
