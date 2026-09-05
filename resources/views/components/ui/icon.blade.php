@props(['name', 'class' => 'h-5 w-5'])

{{-- آیکون از اسپرایت خودمیزبان public/icons.svg — بدون وب‌فونت آیکونی --}}
<svg {{ $attributes->merge(['class' => $class]) }} aria-hidden="true" focusable="false">
    <use href="{{ asset('icons.svg') }}#i-{{ $name }}"></use>
</svg>
