@props([
    'url' => 'hosseinyariam.ir',
    'title' => null,
    'icon' => 'layout-dashboard',
])

{{--
    ماک مرورگر برند-دار — fallback قفل‌شده‌ی گالری پلتفرم (US-M3-2).
    وقتی رکورد اسکرین‌شات ندارد، این به‌جای فضای خالی رندر می‌شود.
--}}
<div {{ $attributes->merge(['class' => 'card overflow-hidden p-0']) }} role="img"
     aria-label="نمای نمونه از رابط {{ $title ?? $url }} — تصویر واقعی هنوز بارگذاری نشده است">
    <div class="flex items-center gap-2 border-b border-line bg-canvas/70 px-4 py-3">
        <span class="h-2.5 w-2.5 rounded-full bg-brand-500/80"></span>
        <span class="h-2.5 w-2.5 rounded-full bg-ink-300"></span>
        <span class="h-2.5 w-2.5 rounded-full bg-brand-300"></span>
        <span class="ms-2 flex-1 truncate rounded-lg border border-line bg-surface px-3 py-1 font-mono text-[10px] text-muted" dir="ltr">{{ $url }}</span>
    </div>

    <div class="grid grid-cols-[56px_1fr]">
        <div class="flex flex-col items-center gap-3 border-e border-line bg-canvas/40 py-4">
            <span class="grid h-8 w-8 place-items-center rounded-xl border border-brand-300/50 bg-brand-100 text-brand-600 dark:border-brand-300/25 dark:bg-brand-500/15 dark:text-brand-300">
                <x-ui.icon :name="$icon" class="h-4 w-4" />
            </span>
            <span class="h-8 w-8 rounded-xl border border-line bg-surface"></span>
            <span class="h-8 w-8 rounded-xl border border-line bg-surface"></span>
            <span class="h-8 w-8 rounded-xl border border-line bg-surface"></span>
        </div>

        <div class="space-y-4 p-4">
            <div class="grid grid-cols-3 gap-3">
                <div class="panel p-3"><span class="block h-1.5 w-10 rounded-full bg-line dark:bg-white/10"></span><span class="mt-2 block h-3 w-8 rounded-full bg-brand-200/70 dark:bg-brand-300/20"></span></div>
                <div class="panel p-3"><span class="block h-1.5 w-12 rounded-full bg-line dark:bg-white/10"></span><span class="mt-2 block h-3 w-8 rounded-full bg-line dark:bg-white/10"></span></div>
                <div class="panel p-3"><span class="block h-1.5 w-8 rounded-full bg-line dark:bg-white/10"></span><span class="mt-2 block h-3 w-10 rounded-full bg-line dark:bg-white/10"></span></div>
            </div>

            <div class="panel p-4">
                <span class="block h-1.5 w-24 rounded-full bg-line dark:bg-white/10"></span>
                <div class="mt-3 flex h-20 items-end gap-1.5" dir="ltr">
                    @foreach ([35, 55, 40, 70, 50, 85, 60, 95, 75, 100] as $i => $h)
                        <span @class([
                            'w-3 rounded-t-md',
                            'bg-brand-500 dark:bg-brand-400' => $h === 95,
                            'bg-brand-400/30 dark:bg-brand-300/25' => $h !== 95,
                        ]) style="height:{{ $h }}%"></span>
                    @endforeach
                </div>
            </div>

            <div class="space-y-2 pb-1">
                <span class="block h-2 w-3/4 rounded-full bg-line dark:bg-white/10"></span>
                <span class="block h-2 w-1/2 rounded-full bg-line dark:bg-white/10"></span>
            </div>
        </div>
    </div>
</div>
