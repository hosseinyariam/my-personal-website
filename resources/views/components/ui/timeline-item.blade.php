@props(['title', 'badge' => null, 'period' => null])

<li class="tl-item">
    <span class="tl-dot" aria-hidden="true"></span>

    <div class="flex flex-wrap items-center gap-2.5">
        <h3 class="text-h3 text-heading">{{ $title }}</h3>
        @if ($badge)
            <x-ui.badge variant="brand">{{ $badge }}</x-ui.badge>
        @endif
    </div>

    @if ($period)
        <p class="mt-1.5 font-mono text-[10px] text-muted" dir="ltr">{{ $period }}</p>
    @endif

    {{ $slot }}
</li>
