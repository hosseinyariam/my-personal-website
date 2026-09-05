{{--
    متای پایه‌ی هر صفحه. در فاز ۵ با JSON-LD و OG کامل تکمیل می‌شود؛
    مقادیر از تنظیمات SEO در دیتابیس می‌آیند، نه از کد (V4).
--}}
@php
    $pageTitle = trim($title ?? '');
    $fullTitle = $pageTitle !== '' ? "{$pageTitle} — {$siteName}" : ($seoTitle ?: $siteName);
    $metaDescription = trim($description ?? '') ?: $seoDescription;
@endphp

<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">
<link rel="canonical" href="{{ $canonical ?? url()->current() }}">
<meta name="theme-color" content="#F6F5F3" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#0E1014" media="(prefers-color-scheme: dark)">
