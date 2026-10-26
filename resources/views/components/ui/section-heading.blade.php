{{--
  سرصفحه‌ی بخش.

  ── چرا ms-auto ──
  در RTL، لینک «مشاهده همه» باید در لبه‌ی مقابل عنوان بنشیند. استفاده از
  margin-inline-start: auto باعث می‌شود لینک به سمت پایان خط (چپ در RTL)
  رانده شود — همان رفتار، بدون کد جهت‌دار.

  ── چرا items-baseline ──
  هم‌ترازی خط پایه‌ی متن عنوان با متن لینک، حتی وقتی اندازه‌ی فونت‌ها
  متفاوت است، ظاهر متعادل می‌دهد.
--}}
@props([
    'title',
    'href' => null,
    'linkLabel' => 'مشاهده همه',
])

<div {{ $attributes->merge(['class' => 'flex items-baseline justify-between gap-4']) }}>
    <h2 class="text-section font-bold">{{ $title }}</h2>

    @if ($href)
        {{-- هدف لمسی: min-h-11 (۴۴px) الزام UI Kit §8.3 است؛ inline-flex
             ارتفاع را روی متن لینک اعمال می‌کند بدون به‌هم‌ریختن خط پایه. --}}
        <a href="{{ $href }}"
           class="ms-auto shrink-0 inline-flex min-h-11 items-center gap-1 rounded-control px-2
                  text-small font-medium text-accent hover:text-accent-hover transition-colors">
            {{ $linkLabel }}
            {{-- در RTL «ادامه» به سمت چپ می‌رود؛ فلش چپ برای «مشاهده همه» درست است. --}}
            <x-ui.icon name="arrow-left" :size="4" />
        </a>
    @endif
</div>
