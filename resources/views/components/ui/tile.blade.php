@props([
    'icon' => null,
    'variant' => 'brand',   // brand | line | ink | deep
    'size' => 'md',         // sm | md | lg | xl
])

@php
    $sizes = [
        'sm' => 'h-9 w-9 rounded-xl text-sm',
        'md' => '',                              // اندازه‌ی پایه‌ی .icon-tile
        'lg' => 'h-14 w-14 text-2xl',
        'xl' => 'h-16 w-16 text-3xl',
    ];
    $glyph = ['sm' => 'h-4 w-4', 'md' => 'h-5 w-5', 'lg' => 'h-7 w-7', 'xl' => 'h-8 w-8'];
@endphp

<span {{ $attributes->merge(['class' => trim("icon-tile tile-{$variant} " . ($sizes[$size] ?? ''))]) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" :class="$glyph[$size] ?? $glyph['md']" />
    @else
        {{ $slot }}
    @endif
</span>
