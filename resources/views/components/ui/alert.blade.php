@props([
    'variant' => 'line',   // brand | deep | line
    'icon' => null,
    'title' => null,
])

@php
    $variants = [
        'brand' => 'alert-brand',
        'deep' => 'alert-deep',
        'line' => 'alert-line',
    ];

    $icon ??= match ($variant) {
        'brand' => 'circle-check',
        'deep' => 'alert-circle',
        default => 'info-circle',
    };

    $iconColor = $variant === 'line' ? 'text-body' : 'text-accent-deep dark:text-accent';
@endphp

<div role="status" {{ $attributes->merge(['class' => 'alert '.($variants[$variant] ?? $variants['line'])]) }}>
    <x-ui.icon :name="$icon" :class="'mt-0.5 h-5 w-5 shrink-0 '.$iconColor" />
    <div class="min-w-0">
        @if ($title)
            <p class="text-sm font-bold text-heading">{{ $title }}</p>
        @endif
        @if (trim($slot) !== '')
            <p @class(['text-xs leading-5 text-body', 'mt-0.5' => $title])>{{ $slot }}</p>
        @endif
    </div>
</div>
