@php
    // نمونه‌های حسابرسی — فقط برای تطبیق بصری با فایل UI Kit
    $statuses = \App\Enums\PlatformStatus::cases();
    $iconNames = collect(preg_split('/\R/', file_get_contents(base_path('build-tools/icons.txt'))))
        ->map(fn ($l) => trim($l))
        ->filter(fn ($l) => $l !== '' && ! str_starts_with($l, '#'))
        ->sort()
        ->values();
@endphp

<x-layouts.app title="حسابرسی UI Kit">
    <div class="space-y-16">

        <header data-reveal>
            <div class="flex flex-wrap items-center gap-3">
                <span class="kicker">DEV ONLY · LIGHT GLASS</span>
                <span class="h-px flex-1 bg-line"></span>
                <span class="route" dir="ltr">GET /dev/kit</span>
            </div>
            <h1 class="mt-3 text-h1 text-heading">حسابرسی کامپوننت‌های <code class="font-mono text-h2 text-accent">x-ui</code></h1>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-body">
                نگاشت ۱:۱ جدول ۸.۲ سند PRD. هر کامپوننت باید عیناً با فایل کیت تطبیق داشته باشد،
                در هر دو تم و در بریک‌پوینت‌های ۳۶۰ تا ۱۴۴۰ پیکسل.
            </p>
        </header>

        {{-- ── ۰۱ رنگ و توکن ───────────────────────────────── --}}
        <section data-reveal>
            <h2 class="text-h2 text-heading">۰۱ — توکن‌های رنگ</h2>
            <div class="mt-5 space-y-6">
                <div>
                    <p class="mb-3 text-[11px] font-bold text-muted">برند</p>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                        @foreach (['700' => '#B23417', '500' => '#F2542D', '300' => '#FF9F6B', '200' => '#FFC19E', '100' => '#FFE3D1', '50' => '#FFF4ED'] as $step => $hex)
                            <button type="button" class="card card-io p-3 text-start" data-copy="{{ $hex }}">
                                <span class="block h-14 rounded-xl border border-line bg-brand-{{ $step }}"></span>
                                <span class="mt-3 block text-xs font-bold text-heading">brand · {{ $step }}</span>
                                <span class="mt-0.5 block font-mono text-[10px] text-muted" dir="ltr">{{ $hex }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="mb-3 text-[11px] font-bold text-muted">سطح‌ها — نقش معنایی (با تم عوض می‌شوند)</p>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach (['canvas' => 'بومِ صفحه', 'surface' => 'سطحِ کارت', 'line' => 'خط و حاشیه', 'accent' => 'لهجه'] as $token => $role)
                            <div class="card p-3">
                                <span class="block h-14 rounded-xl border border-line bg-{{ $token }}"></span>
                                <span class="mt-3 block text-xs font-bold text-heading">{{ $token }}</span>
                                <span class="mt-0.5 block text-[10px] text-muted">{{ $role }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ── ۰۲ تایپوگرافی ───────────────────────────────── --}}
        <section data-reveal>
            <h2 class="text-h2 text-heading">۰۲ — تایپوگرافی</h2>
            <div class="card mt-5 space-y-5 p-6 sm:p-8">
                <p class="text-display text-heading">دیسپلی — نمایشی</p>
                <p class="text-h1 text-heading">تیتر یک — ساختنِ محصول واقعی</p>
                <p class="text-h2 text-heading">تیتر دو — معماری داده‌محور</p>
                <p class="text-h3 text-heading">تیتر سه — جزئیات پیاده‌سازی</p>
                <p class="text-sm leading-8 text-body">متن بدنه با نیم‌فاصله‌ی درست و ارقام فارسی: {{ fa_digits('1403/08/12') }} — هشت‌ سال تجربه.</p>
                <p class="text-[11px] text-muted">کپشن — توضیح فرعی و فراداده</p>
                <p class="font-mono text-xs text-heading" dir="ltr">JetBrains Mono · v1.0.0 · /platforms/vinora</p>
            </div>
        </section>

        {{-- ── ۰۳ دکمه‌ها ──────────────────────────────────── --}}
        <section data-reveal>
            <h2 class="text-h2 text-heading">۰۳ — دکمه‌ها <code class="route" dir="ltr">x-ui.button</code></h2>
            <div class="card mt-5 space-y-8 p-6 sm:p-8">
                <div>
                    <p class="mb-4 text-[11px] font-bold text-muted">نوع</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-ui.button variant="primary">شروع همکاری</x-ui.button>
                        <x-ui.button variant="glass" icon="download">دانلود رزومه</x-ui.button>
                        <x-ui.button variant="ghost">بیشتر بدانید</x-ui.button>
                        <x-ui.button variant="danger" icon="x">حذف</x-ui.button>
                    </div>
                </div>
                <div>
                    <p class="mb-4 text-[11px] font-bold text-muted">اندازه</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <x-ui.button variant="glass" size="sm">کوچک</x-ui.button>
                        <x-ui.button variant="primary" size="md">متوسط</x-ui.button>
                        <x-ui.button variant="glass" size="lg" icon="mail">بزرگ</x-ui.button>
                        <x-ui.button variant="primary" disabled>غیرفعال</x-ui.button>
                    </div>
                </div>
                <div>
                    <p class="mb-4 text-[11px] font-bold text-muted">فقط آیکون <code class="route" dir="ltr">x-ui.icon-button</code></p>
                    <div class="flex flex-wrap gap-3">
                        <x-ui.icon-button icon="brand-github" label="گیت‌هاب" />
                        <x-ui.icon-button icon="brand-telegram" label="تلگرام" />
                        <x-ui.icon-button icon="copy" label="کپی نمونه" data-copy="brand-500 · #F2542D" />
                        <x-ui.icon-button icon="bell" label="اعلان‌ها" size="sm" />
                    </div>
                </div>
            </div>
        </section>

        {{-- ── ۰۴ نشان و چیپ ───────────────────────────────── --}}
        <section data-reveal>
            <h2 class="text-h2 text-heading">۰۴ — نشان‌ها و چیپ‌ها</h2>
            <div class="card mt-5 space-y-8 p-6 sm:p-8">
                <div>
                    <p class="mb-4 text-[11px] font-bold text-muted">نشان وضعیت — از <code class="font-mono" dir="ltr">PlatformStatus</code></p>
                    <div class="flex flex-wrap gap-2.5">
                        @foreach ($statuses as $status)
                            <x-ui.badge
                                :variant="$status->badgeVariant()"
                                :live="$status->isLive()"
                                :icon="$status->icon()"
                                :class="$status === \App\Enums\PlatformStatus::Archived ? 'opacity-70' : ''"
                            >{{ $status->label() }}</x-ui.badge>
                        @endforeach
                        <x-ui.badge variant="deep" icon="alert-circle">منقضی</x-ui.badge>
                    </div>
                </div>
                <div>
                    <p class="mb-4 text-[11px] font-bold text-muted">چیپ و تگ</p>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.chip>Laravel</x-ui.chip>
                        <x-ui.chip>Tailwind CSS</x-ui.chip>
                        <x-ui.chip variant="interactive">قابل انتخاب</x-ui.chip>
                        <x-ui.chip variant="tag">دیزاین‌سیستم</x-ui.chip>
                        <x-ui.chip variant="tag">معماری</x-ui.chip>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── ۰۵ فرم ──────────────────────────────────────── --}}
        <section data-reveal>
            <h2 class="text-h2 text-heading">۰۵ — فرم <code class="route" dir="ltr">x-ui.input</code></h2>
            <div class="card mt-5 p-6 sm:p-8">
                <div class="grid gap-6 sm:grid-cols-2">
                    <x-ui.input name="kit_name" label="نام و نام‌خانوادگی" placeholder="مثلاً حسین یاری" hint="همان‌طور که در ایمیل رسمی می‌نویسید." />
                    <x-ui.input name="kit_email" type="email" label="ایمیل" icon="mail" placeholder="you@example.com" dir="ltr" hint="فقط برای پاسخ‌گویی." />
                    <x-ui.input name="kit_purpose" label="هدف تماس" :options="\App\Enums\ContactPurpose::options()" />
                    <div>
                        <p class="label">سوییچ</p>
                        <div class="panel divide-y divide-line">
                            <div class="flex items-center justify-between gap-3 px-4 py-3">
                                <span class="text-[13px] text-body">کپی پاسخ برایم بفرست</span>
                                <button type="button" class="switch" role="switch" aria-checked="false" aria-label="کپی پاسخ"><span class="knob"></span></button>
                            </div>
                            <div class="flex items-center justify-between gap-3 px-4 py-3">
                                <span class="text-[13px] text-body">فقط پروژه‌های محصولی</span>
                                <button type="button" class="switch is-on" role="switch" aria-checked="true" aria-label="پروژه‌های محصولی"><span class="knob"></span></button>
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <x-ui.input name="kit_message" label="پیام" :rows="4" maxlength="500" counter placeholder="از چالش محصول‌تان بگویید…" />
                    </div>
                    <div class="sm:col-span-2">
                        <p class="mb-2 text-[11px] font-bold text-muted">حالت خطا <code class="route" dir="ltr">input-error</code></p>
                        <input class="input input-error" value="hossein@@mail" dir="ltr" readonly aria-label="نمونه‌ی خطا">
                        <p class="hint-error"><x-ui.icon name="alert-circle" class="h-3.5 w-3.5" />قالب ایمیل درست نیست.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── ۰۶ کارت، کاشی، آواتار ───────────────────────── --}}
        <section data-reveal>
            <h2 class="text-h2 text-heading">۰۶ — کارت، کاشی و آواتار</h2>
            <div class="mt-5 grid gap-5 lg:grid-cols-2">
                <x-ui.card class="p-6">
                    <div class="flex items-start gap-4">
                        <x-ui.tile icon="layout-dashboard" variant="brand" />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-h3 text-heading">کارت پایه</h3>
                                <x-ui.badge variant="brand">برچسب</x-ui.badge>
                            </div>
                            <p class="mt-1 text-xs text-muted">زیرعنوان یا توضیح کوتاه</p>
                        </div>
                    </div>
                    <p class="mt-4 text-sm leading-7 text-body">شیشه‌ی روشن روی مشِ برند عمق می‌گیرد و نورِ پس‌زمینه درونش حرکت می‌کند.</p>
                    <div class="divider my-5"></div>
                    <div class="flex items-center justify-between">
                        <x-ui.button variant="glass" size="sm">عملیات</x-ui.button>
                        <x-ui.link-arrow href="#">جزئیات بیشتر</x-ui.link-arrow>
                    </div>
                </x-ui.card>

                <x-ui.card interactive class="p-6">
                    <div class="flex flex-wrap items-center gap-4">
                        <x-ui.avatar name="حسین یاری" size="lg" />
                        <div class="min-w-0">
                            <h3 class="text-h3 text-heading">کارت تعاملی</h3>
                            <p class="mt-0.5 text-xs text-muted">با هاور بالا می‌آید — <code class="font-mono" dir="ltr">card-io</code></p>
                        </div>
                        <span class="ms-auto"><x-ui.badge variant="brand" live>زنده</x-ui.badge></span>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <x-ui.tile icon="rocket" variant="brand" />
                        <x-ui.tile icon="chart-bar" variant="line" />
                        <x-ui.tile icon="code" variant="ink" />
                        <x-ui.tile icon="database" variant="deep" />
                    </div>
                </x-ui.card>
            </div>
        </section>

        {{-- ── ۰۷ ناوبری ───────────────────────────────────── --}}
        <section data-reveal>
            <h2 class="text-h2 text-heading">۰۷ — ناوبری</h2>
            <div class="mt-5 grid gap-5 lg:grid-cols-2">
                <div class="card p-6">
                    <p class="mb-4 text-[11px] font-bold text-muted">تب‌ها <code class="route" dir="ltr">x-ui.tabs</code></p>
                    <x-ui.tabs :tabs="['problem' => 'مسئله', 'solution' => 'راه‌حل', 'features' => 'امکانات']">
                        <x-ui.tab-panel name="problem" active>
                            <p class="text-sm leading-8 text-body">پنل «مسئله» — با فلش چپ/راست هم قابل جابه‌جایی است.</p>
                        </x-ui.tab-panel>
                        <x-ui.tab-panel name="solution">
                            <p class="text-sm leading-8 text-body">پنل «راه‌حل».</p>
                        </x-ui.tab-panel>
                        <x-ui.tab-panel name="features">
                            <p class="text-sm leading-8 text-body">پنل «امکانات».</p>
                        </x-ui.tab-panel>
                    </x-ui.tabs>
                </div>
                <div class="card space-y-6 p-6">
                    <div>
                        <p class="mb-4 text-[11px] font-bold text-muted">مسیر راهنما</p>
                        <x-ui.breadcrumb :items="[
                            ['label' => 'خانه', 'url' => route('home')],
                            ['label' => 'پلتفرم‌ها', 'url' => route('platforms.index')],
                            ['label' => 'وینورا'],
                        ]" />
                    </div>
                    <div>
                        <p class="mb-4 text-[11px] font-bold text-muted">صفحه‌بندی</p>
                        <div class="flex items-center gap-2">
                            <span class="page-btn is-disabled"><x-ui.icon name="chevron-right" class="h-3.5 w-3.5" /></span>
                            <span class="page-btn is-active">۱</span>
                            <span class="page-btn">۲</span>
                            <span class="page-btn">۳</span>
                            <span class="page-btn"><x-ui.icon name="chevron-left" class="h-3.5 w-3.5" /></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── ۰۸ تایم‌لاین ────────────────────────────────── --}}
        <section data-reveal>
            <h2 class="text-h2 text-heading">۰۸ — تایم‌لاین <code class="route" dir="ltr">x-ui.timeline</code></h2>
            <div class="card mt-5 p-6 sm:p-8">
                <x-ui.timeline>
                    <x-ui.timeline-item title="توسعه‌دهنده‌ی نرم‌افزار" badge="وینورا" period="1403 — now">
                        <ul class="mt-3 space-y-2 text-sm leading-7 text-body">
                            <li class="flex gap-2.5"><x-ui.icon name="point-filled" class="mt-1.5 h-3 w-3 shrink-0 text-accent-deep dark:text-accent" /><span>نمونه‌ی ردیف دستاورد</span></li>
                            <li class="flex gap-2.5"><x-ui.icon name="point-filled" class="mt-1.5 h-3 w-3 shrink-0 text-accent-deep dark:text-accent" /><span>ردیف دوم</span></li>
                        </ul>
                    </x-ui.timeline-item>
                    <x-ui.timeline-item title="نقش پیشین" badge="سازمان" period="1400 — 1403" />
                </x-ui.timeline>
            </div>
        </section>

        {{-- ── ۰۹ بازخورد و حالت خالی ──────────────────────── --}}
        <section data-reveal>
            <h2 class="text-h2 text-heading">۰۹ — بازخورد و حالت‌ها</h2>
            <div class="mt-5 grid gap-5 lg:grid-cols-2">
                <div class="card space-y-3 p-6">
                    <p class="mb-1 text-[11px] font-bold text-muted">هشدار درون‌صفحه‌ای <code class="route" dir="ltr">x-ui.alert</code></p>
                    <x-ui.alert variant="brand" title="رزومه دانلود شد.">فایل در پوشه‌ی Downloads شماست.</x-ui.alert>
                    <x-ui.alert variant="deep" title="ارسال پیام ناموفق بود.">اتصال اینترنت را بررسی کنید و دوباره تلاش کنید.</x-ui.alert>
                    <x-ui.alert variant="line" title="نسخه‌ی ۱.۰ منتشر شد.">پالت روشن + مش گرادیان + تم تاریک مشتق‌شده.</x-ui.alert>
                </div>
                <div class="card p-6">
                    <p class="mb-4 text-[11px] font-bold text-muted">توست</p>
                    <div class="flex flex-wrap gap-3">
                        <x-ui.button variant="glass" size="sm" data-toast="success|کپی شد|متن توکن در کلیپ‌بورد است.">موفقیت</x-ui.button>
                        <x-ui.button variant="glass" size="sm" data-toast="error|ارسال نشد|اتصال اینترنت را بررسی کنید.">خطا</x-ui.button>
                        <x-ui.button variant="glass" size="sm" data-toast="info|نسخه‌ی ۱.۰|این کیت زنده است.">اطلاع</x-ui.button>
                    </div>
                </div>
                <div class="card lg:col-span-2">
                    <x-ui.empty-state icon="notes" title="هنوز یادداشتی وجود ندارد" action="رفتن به وبلاگ" :href="route('blog.index')">
                        وقتی اولین یادداشت فنی منتشر شد، همین‌جا لیست می‌شود.
                    </x-ui.empty-state>
                </div>
            </div>
        </section>

        {{-- ── ۱۰ ماک مرورگر ───────────────────────────────── --}}
        <section data-reveal>
            <h2 class="text-h2 text-heading">۱۰ — ماک مرورگر <code class="route" dir="ltr">x-ui.browser-mock</code></h2>
            <p class="mt-2 text-sm leading-7 text-body">fallback قفل‌شده‌ی گالری پلتفرم — وقتی رکورد اسکرین‌شات ندارد (US-M3-2).</p>
            <div class="mt-5 max-w-lg">
                <x-ui.browser-mock url="vinora.hosseinyariam.ir" title="وینورا" icon="plant-2" />
            </div>
        </section>

        {{-- ── ۱۱ اسپرایت آیکون ────────────────────────────── --}}
        <section data-reveal>
            <h2 class="text-h2 text-heading">۱۱ — اسپرایت آیکون ({{ fa_digits($iconNames->count()) }} گلیف)</h2>
            <p class="mt-2 text-sm leading-7 text-body">خودمیزبان از <code class="font-mono" dir="ltr">public/icons.svg</code> — فقط گلیف‌های استفاده‌شده. برای کپی نام، روی هرکدام بزنید.</p>
            <div class="card mt-5 grid grid-cols-3 gap-2 p-5 sm:grid-cols-5 lg:grid-cols-8">
                @foreach ($iconNames as $name)
                    <button type="button" class="panel flex flex-col items-center gap-2 p-3 transition-colors hover:border-brand-300/60" data-copy="{{ $name }}">
                        <x-ui.icon :name="$name" class="h-5 w-5 text-heading" />
                        <span class="w-full truncate text-center font-mono text-[9px] text-muted" dir="ltr">{{ $name }}</span>
                    </button>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.app>
