@props([
    'variant' => 'primary',   // primary | glass | ghost | danger
    'size' => 'md',           // sm | md | lg
    'href' => null,
    'icon' => null,
    'iconAfter' => null,
])

@php
    $classes = trim("btn btn-{$variant} btn-{$size}");
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
