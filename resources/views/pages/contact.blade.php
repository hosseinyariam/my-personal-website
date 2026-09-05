<x-layouts.app title="تماس" description="برای فرصت شغلی، درخواست خدمت یا هر گفت‌وگوی دیگری بنویسید.">
    <div class="space-y-5">
        <div data-reveal>
            <x-ui.breadcrumb :items="[
                ['label' => 'خانه', 'url' => route('home')],
                ['label' => 'تماس'],
            ]" />
        </div>

        <div class="grid gap-5 lg:grid-cols-[340px_1fr]" data-reveal>

            {{-- ── راه‌های ارتباطی ──────────────────────────── --}}
            <div class="space-y-5">
                @if ($socialLinks->isNotEmpty())
                    <div class="card p-6">
                        <p class="kicker">راه‌های ارتباطی</p>
                        <ul class="mt-5 space-y-5">
                            @foreach ($socialLinks as $link)
                                @php
                                    // برای ایمیل/تلگرام مقدارِ قابل‌کپی را نشان می‌دهیم، نه URL کامل
                                    $display = \Illuminate\Support\Str::of($link->url)
                                        ->replaceFirst('mailto:', '')
                                        ->replaceFirst('https://', '')
                                        ->replaceFirst('http://', '')
                                        ->rtrim('/')
                                        ->value();
                                @endphp
                                <li class="flex items-center gap-3.5">
                                    <x-ui.tile :icon="$link->icon" :variant="$loop->first ? 'brand' : 'line'" />
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[11px] text-muted">{{ $link->label }}</p>
                                        <a href="{{ $link->url }}"
                                           target="_blank" rel="noopener noreferrer me"
                                           class="block truncate font-mono text-xs text-heading transition-colors hover:text-accent-deep dark:hover:text-accent"
                                           dir="ltr">{{ $display }}</a>
                                    </div>
                                    <x-ui.icon-button
                                        icon="copy"
                                        :label="'کپی ' . $link->label"
                                        size="sm"
                                        :data-copy="$display"
                                    />
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($responseNote)
                    <div class="card p-6">
                        <div class="flex items-center gap-2.5">
                            <span class="live-dot"></span>
                            <p class="text-sm font-bold text-heading">پاسخ‌گویی</p>
                        </div>
                        <p class="mt-3 text-xs leading-6 text-body">{{ $responseNote }}</p>
                    </div>
                @endif
            </div>

            {{-- ── فرم ─────────────────────────────────────── --}}
            <div class="card p-7 sm:p-8">
                @if (session('contact_sent'))
                    {{-- حالت موفقیت (US-M5-1) --}}
                    <div class="py-10 text-center">
                        <x-ui.tile icon="circle-check" variant="brand" size="xl" class="mx-auto" />
                        <h1 class="mt-5 text-h2 text-heading">پیام‌تان رسید</h1>
                        <p class="mx-auto mt-3 max-w-sm text-sm leading-7 text-body">
                            ممنون که نوشتید. پیام‌تان ثبت شد و در اولین فرصت پاسخ می‌دهم.
                        </p>
                        <x-ui.button :href="route('contact.show')" variant="glass" size="md" class="mt-6">
                            ارسال پیام دیگر
                        </x-ui.button>
                    </div>
                @else
                    <p class="kicker">فرم تماس</p>
                    <h1 class="mt-2 text-h3 text-heading">برایم بنویسید</h1>

                    @if ($errors->any())
                        <x-ui.alert variant="deep" title="فرم کامل نیست" class="mt-5">
                            فیلدهای مشخص‌شده را بررسی کنید و دوباره ارسال کنید.
                        </x-ui.alert>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="mt-6 space-y-5">
                        @csrf

                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-ui.input
                                name="name"
                                label="نام"
                                placeholder="نام شما"
                                autocomplete="name"
                                required
                            />
                            <x-ui.input
                                name="email"
                                type="email"
                                label="ایمیل"
                                placeholder="you@example.com"
                                autocomplete="email"
                                dir="ltr"
                                required
                            />
                        </div>

                        <x-ui.input
                            name="purpose"
                            label="هدف تماس"
                            :options="$purposes"
                            :value="$selectedPurpose"
                            hint="کمک می‌کند سریع‌تر و دقیق‌تر پاسخ بدهم."
                            required
                        />

                        <x-ui.input
                            name="message"
                            label="پیام"
                            :rows="5"
                            maxlength="1000"
                            counter
                            placeholder="از پروژه یا فرصتی که در ذهن دارید بگویید…"
                            required
                        />

                        {{-- honeypot — برای کاربر نامرئی، برای ربات جذاب (D11) --}}
                        <div class="absolute h-px w-px overflow-hidden opacity-0" aria-hidden="true">
                            <label for="website">وب‌سایت (این فیلد را خالی بگذارید)</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="flex flex-wrap items-center gap-4">
                            <x-ui.button type="submit" variant="primary" size="md" icon="send" data-track="contact_submit">
                                ارسال پیام
                            </x-ui.button>
                            <p class="hint mt-0">ایمیل شما فقط برای پاسخ‌گویی استفاده می‌شود و جایی منتشر نمی‌شود.</p>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
