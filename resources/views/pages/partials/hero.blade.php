{{--
  بخش Hero — پاسخ به «من چه کسی هستم؟»

  ── تصمیم D12 ──
  CTA اصلی «آشنایی با من» است و ثانویه «مشاهده پروژه‌ها». مستندات
  در این مورد تناقض داشتند (Strategy: «آشنایی با من» اصلی؛ UI Kit:
  «مشاهده پروژه‌ها» اصلی). Strategy مبناست چون استراتژی برند است و
  اصل «Identity First» (IA:599) معرفی هویت را بر نشان‌دادن کار
  مقدم می‌داند.

  ── نکته‌ی محتوایی ──
  عنوان و متن از پروفایل می‌آید. هیچ متن ثابتی در این فایل نیست —
  تغییر متن نیازمند دیپلوی نیست.
--}}

<section class="pt-14 pb-16 md:pt-20 md:pb-24" aria-labelledby="hero-heading">
    <div class="container-page">
        <div class="max-w-3xl">

            @if ($profile->professional_title)
                {{--
                  عنوان حرفه‌ای انگلیسی است، پس `dir="ltr"` می‌گیرد تا
                  ترتیب کلمات و علائم درست بماند («Software Engineer &
                  Technology Entrepreneur»).

                  اما `text-align: end` با dir=ltr به چپ می‌چسبد، در حالی
                  که بقیه‌ی بلوک فارسی راست‌چین است. نتیجه: دو خط
                  هم‌بلوک با ترازبندی متضاد — ظاهر شکسته. پس `text-end`
                  صریح می‌گذاریم که در RTL به راست برمی‌گردد.

                  `w-fit` لازم است چون یک عنصر block تمام عرض را می‌گیرد
                  و `text-align` بی‌اثر می‌ماند.
                --}}
                <p dir="ltr"
                   class="w-fit text-label font-semibold text-accent mb-4 text-end">
                    {{ $profile->professional_title }}
                </p>
            @endif

            <h1 id="hero-heading" class="text-hero font-bold tracking-tight text-balance">
                {{ $profile->display_name }}
            </h1>

            @if ($profile->positioning_statement)
                <p class="mt-5 text-card text-text-muted leading-relaxed max-w-2xl">
                    {{ $profile->positioning_statement }}
                </p>
            @elseif ($profile->short_bio)
                <p class="mt-5 text-card text-text-muted leading-relaxed max-w-2xl">
                    {{ $profile->short_bio }}
                </p>
            @endif

            {{-- وضعیت در دسترس بودن — فقط اگر واقعاً تنظیم شده باشد.
                 ادعای «آماده‌ی همکاری» بدون اینکه مدیر آن را تأیید کند،
                 یک ادعای بی‌پشتوانه است. --}}
            @if ($profile->availability_status)
                <p class="mt-6 inline-flex items-center gap-2 badge badge-success live-dot">
                    {{ $profile->availability_status }}
                </p>
            @endif

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('biography') }}" class="btn btn-primary">
                    آشنایی با من
                    <x-ui.icon name="arrow-left" class="size-4" />
                </a>

                @if (\App\Models\Module::isEnabled('projects') && \App\Models\Project::query()->listable()->exists())
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                        مشاهده پروژه‌ها
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
