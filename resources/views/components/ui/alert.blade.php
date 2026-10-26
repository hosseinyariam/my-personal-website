{{--
  هشدار/اطلاع‌رسانی درون‌خطی.

  ── چرا role متفاوت است ──
  role="status" یک ناحیه‌ی polite است: صفحه‌خوان آن را بعد از اتمام
  خواندن فعلی اعلام می‌کند — مناسب تأیید موفقیت یا اطلاع.
  role="alert" فوری (assertive) است و فوراً قطع می‌کند — فقط برای
  هشدار و خطا که کاربر باید بدون تأخیر بداند.

  ── چرا رنگ تنها حامل معنا نیست ──
  هر واریانت یک آیکون متمایز دارد (الزام WCAG: «عدم اتکای صرف به رنگ»).
  آیکون aria-hidden است چون متن پیام معنا را می‌رساند.
--}}
@props([
    'variant' => 'info',
    'title' => null,
])

@php
    // نگاشت بسته: کلاس‌های soft پس‌زمینه + رنگ متن از توکن‌های @theme.
    [$boxClass, $iconName, $role] = match ($variant) {
        'success' => ['bg-success-soft text-success border-success/30', 'check', 'status'],
        'warning' => ['bg-warning-soft text-warning border-warning/30', 'alert', 'alert'],
        'danger' => ['bg-danger-soft text-danger border-danger/30', 'alert', 'alert'],
        default => ['bg-info-soft text-info border-info/30', 'info', 'status'],
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-3 rounded-control border p-4 '.$boxClass]) }}
     role="{{ $role }}">
    <x-ui.icon :name="$iconName" :size="5" class="mt-0.5" />

    <div class="space-y-1 min-w-0">
        @if ($title)
            <p class="text-small font-semibold">{{ $title }}</p>
        @endif

        {{-- متن پیام رنگ متن اصلی سایت را می‌گیرد تا روی پس‌زمینه‌ی soft
             کنتراست AA حفظ شود؛ رنگ واریانت فقط برای آیکون و عنوان می‌ماند. --}}
        <div class="text-small text-text">{{ $slot }}</div>
    </div>
</div>
