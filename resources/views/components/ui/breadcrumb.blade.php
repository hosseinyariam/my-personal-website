@props(['items'])   {{-- [['label' => ..., 'url' => ...|null], ...] --}}

<nav aria-label="مسیر راهنما" {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2 text-muted']) }}>
    @foreach ($items as $i => $item)
        @if (! $loop->first)
            <x-ui.icon name="chevron-left" class="h-3.5 w-3.5" />
        @endif

        @if (! empty($item['url']) && ! $loop->last)
            <a href="{{ $item['url'] }}" class="crumb">{{ $item['label'] }}</a>
        @else
            <span class="text-[13px] font-bold text-heading" aria-current="page">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
