{{--
  Footer.

  ── نکته‌ی محتوایی ──
  کانال‌های ارتباطی فقط از جدول `contact_channels` می‌آیند و فقط
  آن‌هایی که هم فعال و هم عمومی هستند. هیچ ایمیل یا لینکی در این
  فایل هاردکد نشده — الزام «هیچ حساب اجتماعی را حدس نزن» (IA:321).

  ── سال شمسی ──
  سال از Persian::date می‌آید، نه از date('Y'). سال میلادی در
  سایت فارسی نادرست است.
--}}
@php
    $channels = app(\App\Services\SeoService::class)->publicContactChannels();
@endphp

<footer class="border-t border-border bg-surface mt-16 md:mt-24">
    <div class="container-page py-10 md:py-12">
        <div class="grid gap-8 md:grid-cols-3">

            {{-- هویت --}}
            <div class="md:col-span-2">
                <div class="flex items-center gap-2.5 mb-3">
                    <span class="grid size-8 place-items-center rounded-control bg-accent text-white text-sm font-bold"
                          aria-hidden="true">ح</span>
                    <span class="font-bold">{{ config('app.name') }}</span>
                </div>
                <p class="text-small text-text-muted max-w-md leading-relaxed">
                    مهندس نرم‌افزار و کارآفرین فناوری — مسائل واقعی را به راهکارهای فناورانه و محصولات کاربردی تبدیل می‌کنم.
                </p>
            </div>

            {{-- ارتباط --}}
            <div>
                <h2 class="text-small font-semibold mb-3">ارتباط</h2>
                @if ($channels->isEmpty())
                    <p class="text-small text-text-muted">
                        اطلاعات تماس در حال تکمیل است.
                    </p>
                @else
                    <ul class="space-y-2">
                        @foreach ($channels as $channel)
                            <li>
                                <a href="{{ $channel->href() }}"
                                   @if ($channel->isExternal()) target="_blank" rel="noopener noreferrer" @endif
                                   class="text-small text-text-muted hover:text-accent transition-colors inline-flex items-center gap-1.5">
                                    {{ $channel->label }}
                                    @if ($channel->isExternal())
                                        <svg class="size-3.5 opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        <span class="sr-only">(در پنجره‌ی جدید)</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-border flex flex-wrap items-center justify-between gap-3">
            <p class="text-label text-text-subtle">
                © {{ \App\Support\Persian::date(now(), 'yyyy') }} {{ config('app.name') }}
            </p>

            <div class="flex items-center gap-4">
                <a href="{{ route('feed') }}" class="text-label text-text-subtle hover:text-accent transition-colors">
                    خوراک RSS
                </a>
                <a href="{{ route('sitemap') }}" class="text-label text-text-subtle hover:text-accent transition-colors">
                    نقشه سایت
                </a>
            </div>
        </div>
    </div>
</footer>
