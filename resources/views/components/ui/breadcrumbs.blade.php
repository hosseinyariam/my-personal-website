{{--
  مسیر صفحه (Breadcrumbs).

  ── چرا ol و نه div ──
  مسیر یک فهرست مرتب است؛ ol معنای ترتیب را به صفحه‌خوان می‌رساند و
  امکان پرش بین آیتم‌ها را می‌دهد.

  ── چرا آیتم آخر لینک نیست ──
  صفحه‌ی جاری نباید لینک باشد؛ aria-current="page" به صفحه‌خوان
  می‌گوید کاربر همین‌جاست. اگر لینک می‌شد، کنش بی‌نتیجه و گیج‌کننده بود.

  ── چرا جداکننده aria-hidden ──
  جداکننده صرفاً تزئینی است؛ اعلام «‹» توسط صفحه‌خوان نویز است.

  ── چرا با یک آیتم رندر نمی‌شود ──
  مسیر تک‌آیتمی («خانه» در خودِ صفحه‌ی خانه) ناوبری بی‌فایده و یک
  landmark اضافی برای صفحه‌خوان است. آستانه‌ی دو آیتم، مسیر را معنادار
  نگه می‌دارد.
--}}
@props([
    'items' => [],
])

@if (count($items) > 1)
    <nav aria-label="مسیر صفحه" {{ $attributes->merge(['class' => 'text-small']) }}>
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-text-muted">
            @foreach ($items as $index => $item)
                @php $isLast = $index === array_key_last($items); @endphp

                <li class="flex items-center gap-x-2 min-w-0">
                    @if (! $isLast && ! empty($item['url']))
                        {{-- min-h-11 الزام هدف لمسی ۴۴px را تأمین می‌کند؛
                             ترازبندی با flex تا متن وسط ارتفاع بنشیند. --}}
                        <a href="{{ $item['url'] }}"
                           class="inline-flex min-h-11 items-center rounded-control px-1.5 hover:text-accent transition-colors truncate">
                            {{ $item['label'] }}
                        </a>
                    @else
                        {{-- آیتم آخر (یا آیتم بدون لینک) به‌عنوان متن جاری. --}}
                        <span @if ($isLast) aria-current="page" @endif
                              class="inline-flex min-h-11 items-center px-1.5 font-medium text-text truncate">
                            {{ $item['label'] }}
                        </span>
                    @endif

                    @unless ($isLast)
                        {{-- در RTL مسیر از راست به چپ پیش می‌رود، پس جداکننده
                             باید به سمت چپ اشاره کند: «‹». --}}
                        <span class="text-text-subtle select-none" aria-hidden="true">‹</span>
                    @endunless
                </li>
            @endforeach
        </ol>
    </nav>
@endif
