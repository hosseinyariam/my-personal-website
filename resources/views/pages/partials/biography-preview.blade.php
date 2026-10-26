{{-- پیش‌نمایش بیوگرافی در صفحه اصلی — «چه کسی هستم» با عمق بیشتر --}}
<section class="py-14 md:py-20 section-rule" aria-labelledby="biography-preview-heading">
    <div class="container-page">
        <div class="grid gap-8 md:grid-cols-3 md:gap-12">

            <div class="md:col-span-2">
                <h2 id="biography-preview-heading" class="text-section font-bold mb-5">{{ $title }}</h2>

                @if ($profile->short_bio)
                    <div class="prose-fa text-text-muted">
                        <p>{{ $profile->short_bio }}</p>
                    </div>
                @endif

                <a href="{{ route('biography') }}" class="btn btn-secondary mt-6">
                    بیشتر درباره من
                    <x-ui.icon name="arrow-left" class="size-4" />
                </a>
            </div>

            {{-- ارزش‌های اصلی — فقط اگر واقعاً ثبت شده باشند --}}
            @if (! empty($profile->core_values))
                <div>
                    <h3 class="text-small font-semibold mb-4 text-text-muted">ارزش‌های کاری</h3>
                    <ul class="space-y-3">
                        @foreach ((array) $profile->core_values as $value)
                            <li class="flex items-start gap-2.5 text-small text-text-muted">
                                <x-ui.icon name="check" class="size-4 text-accent shrink-0 mt-1" />
                                <span>{{ is_array($value) ? ($value['title'] ?? '') : $value }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</section>
