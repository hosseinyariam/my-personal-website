@props([
    'icon' => 'notes',
    'title',
    'action' => null,       // متن دکمه
    'href' => null,         // مقصد دکمه
])

{{-- دکترین حالت خالی (پیوست ب سند): هرگز صفحه‌ی خالیِ مرده --}}
<div {{ $attributes->merge(['class' => 'flex flex-col items-center px-6 py-14 text-center']) }}>
    <x-ui.tile :icon="$icon" variant="line" size="xl" />
    <h2 class="mt-5 text-h3 text-heading">{{ $title }}</h2>

    @if (trim($slot) !== '')
        <p class="mt-2 max-w-sm text-sm leading-7 text-body">{{ $slot }}</p>
    @endif

    @if ($action && $href)
        <x-ui.button :href="$href" variant="glass" size="md" class="mt-6">{{ $action }}</x-ui.button>
    @endif
</div>
