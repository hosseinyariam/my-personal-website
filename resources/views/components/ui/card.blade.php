@props(['interactive' => false, 'href' => null, 'as' => 'div'])

@php
    $tag = $href ? 'a' : $as;
    $classes = 'card' . (($interactive || $href) ? ' card-io' : '');
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</{{ $tag }}>
