@props(['code', 'icon' => 'alert-circle', 'heading', 'action' => null, 'href' => null])

<x-layouts.app :title="$heading">
    <div class="card p-8 sm:p-12" data-reveal>
        <div class="flex flex-col items-center text-center">
            <x-ui.tile :icon="$icon" variant="line" size="xl" />
            <p class="mt-6 font-mono text-sm font-bold text-accent-deep dark:text-accent" dir="ltr">{{ $code }}</p>
            <h1 class="mt-2 text-h1 text-heading">{{ $heading }}</h1>
            <p class="mt-3 max-w-md text-sm leading-8 text-body">{{ $slot }}</p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <x-ui.button :href="$href ?? route('home')" variant="primary" size="md" icon="home">
                    {{ $action ?? 'بازگشت به خانه' }}
                </x-ui.button>
                <x-ui.button :href="route('contact.show')" variant="glass" size="md" icon="send">
                    گزارش مشکل
                </x-ui.button>
            </div>
        </div>
    </div>
</x-layouts.app>
