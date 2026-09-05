@props(['tabs'])   {{-- آرایه‌ی [key => label] --}}

@php $first = array_key_first($tabs); @endphp

<div data-tabs {{ $attributes }}>
    <div class="panel flex flex-wrap gap-1 p-1.5" role="tablist">
        @foreach ($tabs as $key => $label)
            <button
                type="button"
                role="tab"
                id="tab-{{ $key }}"
                aria-controls="panel-{{ $key }}"
                aria-selected="{{ $key === $first ? 'true' : 'false' }}"
                tabindex="{{ $key === $first ? '0' : '-1' }}"
                data-tab="{{ $key }}"
                @class(['tab', 'is-active' => $key === $first])
            >{{ $label }}</button>
        @endforeach
    </div>
    {{ $slot }}
</div>
