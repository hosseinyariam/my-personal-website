@props(['icon', 'label', 'href' => null, 'size' => 'md'])

@php
    $sizes = ['sm' => 'h-8 w-8 rounded-lg', 'md' => 'h-10 w-10', 'lg' => 'h-11 w-11'];
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="button" @endif
    aria-label="{{ $label }}"
    {{ $attributes->merge(['class' => 'icon-btn ' . ($sizes[$size] ?? $sizes['md'])]) }}
>
    <x-ui.icon :name="$icon" class="h-[1.15em] w-[1.15em]" />
</{{ $tag }}>
