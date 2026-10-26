{{--
  بخش «رویکرد من».

  ── چرا از فلسفه‌ی ثبت‌شده می‌آید ──
  این محتوا از `profile.philosophy` می‌آید، نه از متن ثابت در Blade.
  دلیل: مسیر برند «مسئله را بفهم. بساز. یاد بگیر. ادامه بده» ممکن
  است تکامل یابد و تغییر آن نباید کد بخواهد.

  ── نکته ──
  خطوط تزئینی یا آیکون‌های تزئینی نداریم — مستندات صریحاً
  «آیکون‌های تزئینی» و «افکت‌های سنگین» را نهی کرده‌اند (UI Kit:253-259).
  ساختار از شماره‌گذاری معنادار می‌آید، نه از آیکون بی‌معنا.
--}}
<section class="py-14 md:py-20 section-rule" aria-labelledby="how-i-think-heading">
    <div class="container-page">
        <div class="max-w-3xl">
            <h2 id="how-i-think-heading" class="text-section font-bold mb-5">{{ $title }}</h2>

            <div class="prose-fa text-text-muted">
                {!! nl2br(e($philosophy)) !!}
            </div>
        </div>
    </div>
</section>
