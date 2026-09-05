@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'ogImage' => null,
    'ogType' => 'website',
    'schema' => null,
])

<!DOCTYPE html>
<html lang="fa" dir="rtl" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- اعمال تم ذخیره‌شده پیش از اولین رنگ‌آمیزی — بدون فلیکر (فاز ۱) --}}
    <script>
        try { if (localStorage.getItem('hy-theme') === 'dark') document.documentElement.classList.add('dark'); } catch (e) {}
    </script>

    @include('partials.meta')

    <link rel="preload" href="{{ asset('fonts/vazirmatn-var.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')

    {{-- داده‌ی ساخت‌یافته‌ی هر صفحه --}}
    @isset($schema)
        <x-ui.json-ld :data="$schema" />
    @endisset

    @include('partials.analytics')
</head>

<body id="top" class="bg-canvas font-sans text-body antialiased selection:bg-brand-100 selection:text-brand-700 dark:selection:bg-brand-500/40 dark:selection:text-white">

<a href="#main" class="skip-link">پرش به محتوای اصلی</a>

@include('partials.mesh')
@include('partials.header')

<main id="main" class="relative z-10 mx-auto max-w-6xl px-4 pb-16 pt-28 sm:px-6 sm:pt-32">
    {{ $slot }}
</main>

@include('partials.footer')

{{-- جعبه‌ی توست --}}
<div id="toastBox" class="pointer-events-none fixed bottom-6 end-6 z-[90] flex flex-col gap-3" aria-live="polite" aria-atomic="false"></div>

{{-- پیام‌های فلشِ سمت سرور به توست تبدیل می‌شوند --}}
@foreach (['success' => 'success', 'error' => 'error', 'info' => 'info'] as $key => $type)
    @if (session($key))
        <span hidden data-server-toast data-type="{{ $type }}" data-title="{{ session($key) }}"></span>
    @endif
@endforeach

@stack('scripts')
</body>
</html>
