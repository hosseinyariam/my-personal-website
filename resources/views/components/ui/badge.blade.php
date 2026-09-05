@props([
    'variant' => 'brand',  // brand | line | deep
    'icon' => null,
    'live' => false,
])

<span {{ $attributes->merge(['class' => "badge badge-{$variant}"]) }}>
    @if ($live)
        <span class="live-dot"></span>
    @elseif ($icon)
        <x-ui.icon :name="$icon" class="h-3.5 w-3.5" />
    @endif
    {{ $slot }}
</span>
