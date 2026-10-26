{{--
  دعوت به ارتباط در انتهای صفحه اصلی.

  ── لینک‌های خارجی ──
  لینک‌های شبکه‌های اجتماعی با `rel="noopener noreferrer"` باز می‌شوند.
  `noopener` جلوگیری می‌کند صفحه‌ی مقصد به `window.opener` دسترسی
  داشته باشد (reverse tabnabbing) و `noreferrer` اطلاعات ارجاع را
  نمی‌فرستد.
--}}
<section class="py-14 md:py-20 section-rule" aria-labelledby="contact-cta-heading">
    <div class="container-page">
        <div class="card bg-surface border-0 md:p-10">
            <div class="max-w-2xl">
                <h2 id="contact-cta-heading" class="text-section font-bold mb-3">{{ $title }}</h2>

                <p class="text-body text-text-muted leading-relaxed mb-6">
                    برای همکاری، گفت‌وگو درباره‌ی یک مسئله یا پرسش درباره‌ی محصولات، راه‌های ارتباطی باز است.
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('contact.show') }}" class="btn btn-primary">
                        تماس با من
                        <x-ui.icon name="arrow-left" class="size-4" />
                    </a>

                    @foreach ($channels->take(2) as $channel)
                        <a href="{{ $channel->href() }}"
                           @if ($channel->isExternal()) target="_blank" rel="noopener noreferrer" @endif
                           class="btn btn-secondary">
                            {{ $channel->label }}
                            @if ($channel->isExternal())
                                <x-ui.icon name="external" class="size-4" />
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
