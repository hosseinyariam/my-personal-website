{{--
  آیکون درون‌خطی (inline SVG).

  ── چرا SVG و نه فونت آیکون ──
  فونت آیکون یک درخواست شبکه‌ی اضافه و یک منبع FOIT است؛ برای سایت
  کم‌حجم و بدون وابستگی خارجی، SVG درون‌خطی سبک‌تر و بدون پرش است.

  ── چرا همیشه aria-hidden ──
  آیکون در این کیت هرگز حامل معنا به‌تنهایی نیست (الزام UI Kit §7.7:
  «آیکون تعاملی نام دسترس‌پذیر داشته باشد»). معنا از متن یا aria-label
  والد می‌آید؛ پس آیکون از درخت دسترس‌پذیری حذف می‌شود تا صفحه‌خوان
  محتوای تکراری نخواند.

  ── چرا نگاشت اندازه ──
  Tailwind کلاس‌ها را با اسکن متن فایل پیدا می‌کند، نه در زمان اجرا.
  `size-{{ $size }}` تولید نمی‌شود، پس فهرست بسته‌ی کلاس‌های مجاز
  اینجا نگه داشته می‌شود تا واقعاً در CSS خروجی ظاهر شوند.
--}}
@props([
    'name' => 'cube',
    'size' => 5,
])

@php
    $sizeClass = match ((int) $size) {
        3 => 'size-3',
        4 => 'size-4',
        5 => 'size-5',
        6 => 'size-6',
        8 => 'size-8',
        10 => 'size-10',
        12 => 'size-12',
        default => 'size-5',
    };
@endphp

<svg {{ $attributes->merge(['class' => $sizeClass.' shrink-0']) }}
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     stroke-width="1.75"
     stroke-linecap="round"
     stroke-linejoin="round"
     aria-hidden="true"
     focusable="false">
    @switch($name)
        @case('arrow-left')
            {{-- «قبلی»/«بازگشت» در RTL: نوک به سمت شروع متن، یعنی چپ. --}}
            <path d="M19 12H5M12 19l-7-7 7-7"/>
            @break

        @case('arrow-right')
            <path d="M5 12h14M12 5l7 7-7 7"/>
            @break

        @case('external')
            <path d="M10 6H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            @break

        @case('github')
            <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>
            @break

        @case('linkedin')
            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
            <path d="M2 9h4v12H2z"/>
            <circle cx="4" cy="4" r="2"/>
            @break

        @case('mail')
            <path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
            <path d="M22 7l-10 6L2 7"/>
            @break

        @case('search')
            <path d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z"/>
            @break

        @case('inbox')
            <path d="M22 12h-6l-2 3h-4l-2-3H2"/>
            <path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
            @break

        @case('check')
            <path d="M20 6L9 17l-5-5"/>
            @break

        @case('alert')
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <path d="M12 9v4"/>
            <path d="M12 17h.01"/>
            @break

        @case('info')
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 16v-4"/>
            <path d="M12 8h.01"/>
            @break

        @case('close')
            <path d="M18 6L6 18M6 6l12 12"/>
            @break

        @case('calendar')
            <path d="M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>
            <path d="M8 2v4M16 2v4M3 10h18"/>
            @break

        @case('code')
            <path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/>
            @break

        @case('cube')
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            <path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/>
            @break

        @case('building')
            <path d="M3 21h18"/>
            <path d="M6 21V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v17"/>
            <path d="M10 21v-4h4v4"/>
            <path d="M9 7h2M13 7h2M9 11h2M13 11h2"/>
            @break

        @case('note')
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>
            @break

        @case('route')
            {{-- دو گره و یک مسیر پیوسته: خوانش «مسیر حرفه‌ای» بدون آیکون متافوریک. --}}
            <circle cx="5" cy="19" r="2"/>
            <circle cx="19" cy="5" r="2"/>
            <path d="M7 19h6a4 4 0 0 0 4-4V7"/>
            @break

        @case('sparkle')
            <path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z"/>
            @break

        @default
            {{-- نام ناشناس نباید رندر را بشکند؛ یک نشانگر خنثی جایگزین می‌شود. --}}
            <circle cx="12" cy="12" r="3"/>
    @endswitch
</svg>
