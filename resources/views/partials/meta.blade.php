{{--
    متا، Open Graph و Twitter Card برای هر صفحه‌ی عمومی (فاز ۵).
    همه‌ی مقادیر از دیتابیس می‌آیند — هیچ متن سئویی در کد هاردکد نیست (V4).
--}}
@php
    $pageTitle = trim((string) ($title ?? ''));
    $fullTitle = $pageTitle !== ''
        ? "{$pageTitle} — {$siteName}"
        : ($seoTitle ?: $siteName);

    $metaDescription = trim((string) ($description ?? '')) ?: (string) $seoDescription;

    $image = $ogImage ?? $seoImage;
    $imageUrl = filled($image)
        ? (str_starts_with((string) $image, 'http') ? $image : \Illuminate\Support\Facades\Storage::disk('public')->url($image))
        : null;
@endphp

<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<link rel="canonical" href="{{ $canonical ?? url()->current() }}">

<meta name="theme-color" content="#F6F5F3" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0E1014" media="(prefers-color-scheme: dark)">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="fa_IR">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $canonical ?? url()->current() }}">
@if ($imageUrl)
    <meta property="og:image" content="{{ $imageUrl }}">
    <meta property="og:image:alt" content="{{ $fullTitle }}">
@endif

{{-- Twitter Card --}}
<meta name="twitter:card" content="{{ $imageUrl ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
@if ($imageUrl)
    <meta name="twitter:image" content="{{ $imageUrl }}">
@endif

{{-- پنل مدیریت و صفحات دِو هرگز ایندکس نمی‌شوند --}}
@if (request()->is('admin*') || request()->is('dev/*'))
    <meta name="robots" content="noindex, nofollow">
@endif
