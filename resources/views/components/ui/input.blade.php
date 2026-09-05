@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'icon' => null,
    'rows' => null,          // مقدار غیرخالی = textarea
    'options' => null,       // آرایه‌ی [value => label] = select
    'counter' => false,
    'required' => false,
    'dir' => null,
])

@php
    $id = $attributes->get('id', "f-{$name}");
    $hasError = $errors->has($name);
    $describedBy = collect([
        $hint ? "{$id}-hint" : null,
        $hasError ? "{$id}-error" : null,
    ])->filter()->implode(' ');

    $control = $attributes
        ->except(['id', 'class'])
        ->merge([
            'id' => $id,
            'name' => $name,
            'class' => 'input' . ($hasError ? ' input-error' : '') . ($icon ? ' ps-11' : '') . ($rows ? ' resize-none leading-7' : '') . ($options ? ' cursor-pointer appearance-none pe-10' : ''),
        ]);

    if ($describedBy) {
        $control = $control->merge(['aria-describedby' => $describedBy]);
    }
    if ($hasError) {
        $control = $control->merge(['aria-invalid' => 'true']);
    }
    if ($required) {
        $control = $control->merge(['required' => true]);
    }
    if ($dir) {
        $control = $control->merge(['dir' => $dir]);
    }

    $old = old($name, $value);
@endphp

<div>
    @if ($label)
        <div class="mb-2 flex items-baseline justify-between gap-3">
            <label class="label mb-0" for="{{ $id }}">
                {{ $label }}
                @if ($required)
                    <span class="text-accent-deep dark:text-accent" aria-hidden="true">*</span>
                @endif
            </label>
            @if ($counter)
                <span class="font-mono text-[10px] text-muted" data-counter-for="{{ $id }}" aria-hidden="true"></span>
            @endif
        </div>
    @endif

    <div @class(['relative' => $icon || $options])>
        @if ($icon)
            <x-ui.icon :name="$icon" class="pointer-events-none absolute top-1/2 start-4 h-[1.05rem] w-[1.05rem] -translate-y-1/2 text-muted" />
        @endif

        @if ($rows)
            <textarea {{ $control->merge(['rows' => $rows]) }}>{{ $old }}</textarea>
        @elseif ($options)
            <select {{ $control }}>
                @foreach ($options as $optValue => $optLabel)
                    <option value="{{ $optValue }}" @selected((string) $old === (string) $optValue) class="bg-surface text-heading">{{ $optLabel }}</option>
                @endforeach
            </select>
            <x-ui.icon name="chevron-down" class="pointer-events-none absolute top-1/2 end-4 h-4 w-4 -translate-y-1/2 text-muted" />
        @else
            <input {{ $control->merge(['type' => $type, 'value' => $old]) }}>
        @endif
    </div>

    @if ($hint && ! $hasError)
        <p class="hint" id="{{ $id }}-hint">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="hint-error" id="{{ $id }}-error">
            <x-ui.icon name="alert-circle" class="h-3.5 w-3.5" />
            {{ $message }}
        </p>
    @enderror
</div>
