@props(['href' => '#'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'link-arrow group']) }}>
    {{ $slot }}
    <x-ui.icon name="arrow-left" class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
</a>
