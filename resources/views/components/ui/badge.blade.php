@props([
    'variant' => 'brand',  // brand | line | deep
    'icon' => null,
    'live' => false,
])

@php
    $variants = [
        'brand' => 'badge-brand',
        'line' => 'badge-line',
        'deep' => 'badge-deep',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'badge '.($variants[$variant] ?? $variants['brand'])]) }}>
    @if ($live)
        <span class="live-dot"></span>
    @elseif ($icon)
        <x-ui.icon :name="$icon" class="h-3.5 w-3.5" />
    @endif
    {{ $slot }}
</span>
