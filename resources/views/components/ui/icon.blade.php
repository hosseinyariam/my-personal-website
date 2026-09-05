@props(['name', 'class' => 'h-5 w-5'])

{{--
    آیکون از اسپرایت خودمیزبان public/icons.svg — بدون وب‌فونت آیکونی.

    نکته‌ی مهم: <use> فقط خودِ <symbol> را کلون می‌کند و صفاتِ نمایشیِ
    <svg> ریشه‌ی فایل اسپرایت را به ارث نمی‌برد. بنابراین stroke/fill
    باید همین‌جا — روی svgِ ارجاع‌دهنده — تعریف شود، وگرنه مسیرهای
    خطی با fill پیش‌فرضِ مشکی به لکه تبدیل می‌شوند.
--}}
<svg
    {{ $attributes->merge(['class' => $class]) }}
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
>
    <use href="{{ asset('icons.svg') }}#i-{{ $name }}"></use>
</svg>
