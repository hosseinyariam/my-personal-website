@props([
    'variant' => 'static',  // static | interactive | tag
    'icon' => null,
    'href' => null,
])

@php
    $classes = match ($variant) {
        'interactive' => 'chip',
        'tag' => 'tag-brand',
        default => 'chip-static',
    };
    $tag = $href ? 'a' : 'span';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <x-ui.icon :name="$icon" class="h-3.5 w-3.5" />
    @endif
    {{ $slot }}
</{{ $tag }}>
