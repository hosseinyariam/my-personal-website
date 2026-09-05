@props(['name', 'active' => false])

<div
    data-panel="{{ $name }}"
    id="panel-{{ $name }}"
    role="tabpanel"
    aria-labelledby="tab-{{ $name }}"
    tabindex="0"
    @class(['mt-6', 'hidden' => ! $active])
>
    {{ $slot }}
</div>
