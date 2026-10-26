@extends('layouts.app')

{{--
  جست‌وجوی داخلی.

  ── چرا noindex ──
  این صفحه عمداً `noindex` است (در کنترلر ست می‌شود) چون صفحات نتیجه‌ی
  جست‌وجو محتوای تکراری می‌سازند و ارزش SEO ندارند. مسیر /search هم
  در robots.txt مسدود است.

  ── حالت‌ها ──
  چهار حالت ممکن است: عبارت خیلی کوتاه، بدون نتیجه، با نتیجه، و حالت
  اولیه (بدون عبارت). هر کدام بازخورد مشخص و مفید می‌گیرند — نه صفحه‌ی
  سفید.
--}}

@section('content')
    <div class="container-page py-12 md:py-16">

        <x-ui.page-heading
            title="جست‌وجو"
            subtitle="در پروژه‌ها، محصولات و یادداشت‌ها جست‌وجو کنید." />

        {{-- ── فرم جست‌وجو ââ --}}}
        <form action="{{ route('search') }}" method="GET" class="mt-8 max-w-xl">
            <label for="search-q" class="field-label">عبارت جست‌وجو</label>

            <div class="flex gap-3">
                <input type="search"
                       id="search-q"
                       name="q"
                       value="{{ $query }}"
                       class="field-input"
                       placeholder="مثلاً: لاراول، طراحی، محصول…"
                       autocomplete="off">

                <button type="submit" class="btn btn-primary shrink-0">
                    جست‌وجو
                    <x-ui.icon name="search" class="size-4" />
                </button>
            </div>
        </form>

        {{-- ── حالت ۱: عبارت خیلی کوتاه ââ --}}}
        @if ($tooShort)
            <div class="mt-8 max-w-xl">
                <x-ui.alert variant="warning">
                    عبارت جست‌وجو باید حداقل ۲ کاراکتر باشد.
                </x-ui.alert>
            </div>

        {{-- ── حالت ۲: عبارت داده شده ولی نتیجه‌ای نیست ââ --}}}
        @elseif (filled($query) && $total === 0)
            <div class="mt-10">
                <x-ui.empty-state
                    icon="search"
                    title="نتیجه‌ای پیدا نشد"
                    description="برای «{{ $query }}» چیزی پیدا نشد. می‌توانید عبارت کوتاه‌تری امتحان کنید، یا از فهرست‌های زیر شروع کنید.">
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">پروژه‌ها</a>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">محصولات</a>
                        <a href="{{ route('notes.index') }}" class="btn btn-secondary">یادداشت‌ها</a>
                    </div>
                </x-ui.empty-state>
            </div>

        {{-- ── حالت ۳: نتایج وجود دارد ââ --}}}
        @elseif ($results->isNotEmpty())
            <p class="mt-10 text-small text-text-muted num-fa" role="status">
                {{ \App\Support\Persian::digits($total) }} نتیجه برای «{{ $query }}»
            </p>

            <ul class="mt-6 space-y-4 max-w-3xl">
                @foreach ($results as $result)
                    <li class="card">
                        <div class="mb-2">
                            <x-ui.badge variant="neutral">{{ $result['label'] }}</x-ui.badge>
                        </div>

                        <h2 class="text-card font-semibold mb-2">
                            <a href="{{ $result['url'] }}" class="hover:text-accent transition-colors">
                                {{ $result['title'] }}
                            </a>
                        </h2>

                        @if (filled($result['excerpt']))
                            <p class="text-small text-text-muted leading-relaxed">{{ $result['excerpt'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
