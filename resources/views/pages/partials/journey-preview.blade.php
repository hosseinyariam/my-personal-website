{{--
  پیش‌نمایش مسیر حرفه‌ای در صفحه اصلی.

  ── کلیک‌پذیری مشروط ──
  رویداد ممکن است به موجودیتی وصل باشد که خودش منتشر نشده است.
  `relatedUrl()` این را بررسی می‌کند و اگر هدف قابل نمایش نباشد
  `null` برمی‌گرداند — پس رویداد متن ساده می‌ماند، نه لینک شکسته.
--}}
<section class="py-14 md:py-20 section-rule" aria-labelledby="journey-preview-heading">
    <div class="container-page">
        <x-ui.section-heading
            :title="$title"
            :href="route('journey')"
            id="journey-preview-heading" />

        <div class="mt-8 max-w-2xl">
            <x-ui.timeline>
                @foreach ($events as $event)
                    <x-ui.timeline-item
                        :date="$event->dateLabel()"
                        :title="$event->title"
                        :description="$event->summary"
                        :href="$event->relatedUrl()"
                        :highlight="$event->is_highlight" />
                @endforeach
            </x-ui.timeline>
        </div>
    </div>
</section>
