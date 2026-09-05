<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">آمادگی محتوا</x-slot>

        <x-slot name="description">
            دروازه‌ی تصمیم لانچ: تا وقتی همه‌ی ردیف‌ها سبز نشده‌اند، بخشی از سایت حالت خالی نشان می‌دهد.
        </x-slot>

        <x-slot name="headerEnd">
            @if ($isLaunchReady)
                <x-filament::badge color="success" icon="heroicon-m-check-circle">آماده‌ی لانچ</x-filament::badge>
            @else
                <x-filament::badge color="warning" icon="heroicon-m-exclamation-triangle">
                    {{ fa_digits($pendingCount) }} مورد باقی مانده
                </x-filament::badge>
            @endif
        </x-slot>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($checks as $check)
                <div @class([
                    'flex items-start gap-3 rounded-xl border p-4 transition',
                    'border-primary-200 bg-primary-50 dark:border-primary-500/30 dark:bg-primary-500/10' => $check['ready'],
                    'border-gray-200 bg-white dark:border-white/10 dark:bg-white/5' => ! $check['ready'],
                ])>
                    <div @class([
                        'mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                        'bg-primary-500 text-white' => $check['ready'],
                        'bg-gray-100 text-gray-400 dark:bg-white/10 dark:text-gray-500' => ! $check['ready'],
                    ])>
                        <x-filament::icon
                            :icon="$check['ready'] ? 'heroicon-m-check' : 'heroicon-m-minus'"
                            class="h-5 w-5"
                        />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-baseline justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $check['label'] }}</p>
                            <span class="shrink-0 font-mono text-xs text-gray-500">{{ fa_digits($check['count']) }}</span>
                        </div>

                        <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $check['hint'] }}</p>

                        @unless ($check['ready'])
                            <p class="mt-1.5 text-xs font-medium text-primary-600 dark:text-primary-400">{{ $check['requirement'] }}</p>
                        @endunless

                        @if ($check['url'])
                            <a href="{{ $check['url'] }}"
                               class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary-600 hover:underline dark:text-primary-400">
                                ورود داده
                                <x-filament::icon icon="heroicon-m-arrow-left" class="h-3.5 w-3.5" />
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
