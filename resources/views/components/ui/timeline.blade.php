@props([])

<ol {{ $attributes->merge(['class' => 'timeline space-y-9']) }}>
    {{ $slot }}
</ol>
