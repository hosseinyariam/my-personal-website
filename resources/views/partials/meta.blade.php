{{--
  متادیتای صفحه — SEO، Open Graph و داده‌ی ساختاریافته.

  ورودی: $meta آرایه‌ای از SeoService با کلیدهای:
    title, description, canonical, robots, og_title, og_description,
    og_image, type

  نکته‌ی مهم: هیچ داده‌ی ساختاریافته‌ای اینجا تولید نمی‌شود مگر
  WebSite که فقط نام و آدرس است. اصل «Evidence Over Claims» یعنی
  هیچ Organization/Person/AggregateRating جعلی ساخته نمی‌شود.
--}}
@php
    $meta = $meta ?? [];
    $pageTitle = $meta['title'] ?? config('app.name');
    $pageDescription = $meta['description'] ?? null;
    $canonical = $meta['canonical'] ?? url()->current();
    $robots = $meta['robots'] ?? 'index, follow';
@endphp

<title>{{ $pageTitle }}</title>

@if ($pageDescription)
    <meta name="description" content="{{ $pageDescription }}">
@endif

<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="{{ $robots }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $meta['type'] ?? 'website' }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:locale" content="fa_IR">
<meta property="og:title" content="{{ $meta['og_title'] ?? $pageTitle }}">
<meta property="og:url" content="{{ $canonical }}">

@if ($meta['og_description'] ?? $pageDescription)
    <meta property="og:description" content="{{ $meta['og_description'] ?? $pageDescription }}">
@endif

@if ($meta['og_image'] ?? null)
    <meta property="og:image" content="{{ $meta['og_image'] }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ $meta['og_image'] }}">
@else
    <meta name="twitter:card" content="summary">
@endif

<meta name="twitter:title" content="{{ $meta['og_title'] ?? $pageTitle }}">

@if ($meta['og_description'] ?? $pageDescription)
    <meta name="twitter:description" content="{{ $meta['og_description'] ?? $pageDescription }}">
@endif

@if (filled(config('services.matomo.url')) && config('services.matomo.enabled'))
    <link rel="preconnect" href="{{ config('services.matomo.url') }}" crossorigin>
@endif
