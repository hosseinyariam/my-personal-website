@if ($paginator->hasPages())
    <nav role="navigation" aria-label="صفحه‌بندی" class="flex items-center justify-center gap-2">
        {{-- RTL: «قبلی» به راست، آیکون chevron-right --}}
        @if ($paginator->onFirstPage())
            <span class="page-btn is-disabled" aria-disabled="true" aria-label="صفحه‌ی قبلی">
                <x-ui.icon name="chevron-right" class="h-3.5 w-3.5" />
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="page-btn" aria-label="صفحه‌ی قبلی">
                <x-ui.icon name="chevron-right" class="h-3.5 w-3.5" />
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-1 text-xs text-muted" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-btn is-active" aria-current="page">{{ fa_digits($page) }}</span>
                    @else
                        <a href="{{ $url }}" class="page-btn" aria-label="صفحه‌ی {{ fa_digits($page) }}">{{ fa_digits($page) }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="page-btn" aria-label="صفحه‌ی بعدی">
                <x-ui.icon name="chevron-left" class="h-3.5 w-3.5" />
            </a>
        @else
            <span class="page-btn is-disabled" aria-disabled="true" aria-label="صفحه‌ی بعدی">
                <x-ui.icon name="chevron-left" class="h-3.5 w-3.5" />
            </span>
        @endif
    </nav>
@endif
