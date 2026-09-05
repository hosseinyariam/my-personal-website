@props([
    'name' => '',
    'src' => null,
    'size' => 'md',   // sm | md | lg | xl
])

@php
    $sizes = [
        'sm' => 'h-9 w-9 rounded-xl text-base',
        'md' => 'h-12 w-12 text-xl',
        'lg' => 'h-16 w-16 text-3xl',
        'xl' => 'h-16 w-16 text-3xl sm:h-20 sm:w-20 sm:text-4xl',
    ];
    // مونوگرام از حرف اول نام — رفتار قفل‌شده‌ی «بدون عکس» در پیوست ب سند
    $initial = mb_substr(trim($name), 0, 1) ?: 'ح';
@endphp

<span {{ $attributes->merge(['class' => 'avatar ' . ($sizes[$size] ?? $sizes['md'])]) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $name }}" class="h-full w-full object-cover" loading="lazy" decoding="async">
    @else
        <span aria-hidden="true">{{ $initial }}</span>
        <span class="sr-only">{{ $name }}</span>
    @endif
</span>
