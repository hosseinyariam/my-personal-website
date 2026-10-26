{{--
  Layout اصلی سایت عمومی.

  ساختار: skip-link → header → main → footer
  دلیل هر بخش:
    - skip-link: الزام دسترس‌پذیری برای کاربران صفحه‌کلید. بدون آن،
      کاربر باید از منوی کامل عبور کند تا به محتوا برسد.
    - main با id="main": هدف skip-link و landmark برای screen reader.
    - dir="rtl" روی html: RTL واقعی، نه text-align: right.
--}}
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.meta')

    <meta name="theme-color" content="#2563EB">

    <link rel="preload" href="{{ asset('fonts/vazirmatn-var.woff2') }}" as="font" type="font/woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="min-h-dvh flex flex-col bg-bg text-text antialiased">

    <a href="#main" class="skip-link">پرش به محتوای اصلی</a>

    @include('partials.header')

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>
</html>
