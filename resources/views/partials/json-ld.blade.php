{{--
  داده‌ی ساختاریافته (JSON-LD).

  ── اصل سخت ──
  IA:481 و Strategy:481: «اطلاعات Schema.org و داده‌های هویتی را فقط
  بر اساس اطلاعات واقعی منتشر کن. از ساخت Review، Rating، Organization،
  Person یا آمار غیرواقعی خودداری کن.»

  به همین دلیل این partial فقط چیزی را چاپ می‌کند که از کنترلر
  رسیده باشد — و کنترلرها فقط BreadcrumbList و WebSite می‌سازند.
  هیچ Person، Organization یا AggregateRating در این پروژه ساخته
  نمی‌شود چون اطلاعات تأییدشده‌ای برای پرکردنشان وجود ندارد.

  ── امنیت ──
  خروجی JSON با فلگ‌های امن encode می‌شود تا اگر روزی متن کاربر
  وارد شد، `</script>` نتواند تگ را بشکند. `JSON_HEX_TAG` و
  `JSON_HEX_AMP` این را تضمین می‌کنند.
--}}
@if (! empty($schema))
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endif
