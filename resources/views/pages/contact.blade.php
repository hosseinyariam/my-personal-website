@extends('layouts.app')

{{--
  صفحه‌ی تماس.

  ── کانال‌ها از دیتابیس ──
  هیچ ایمیل، شماره یا لینکی در این فایل هاردکد نیست. کانال‌ها از
  جدول `contact_channels` و فقط آن‌هایی که هم فعال و هم عمومی‌اند
  می‌آیند (scope public). الزام IA:321: «هیچ حساب اجتماعی را حدس نزن».

  ── محافظت فرم ──
  سه لایه: honeypot (فیلد website که با sr-only پنهان است)، throttle
  ۵ در ساعت روی مسیر، و بررسی محتوای اسپم در سرویس.
--}}

@section('content')
    <div class="container-page py-12 md:py-16">

        <x-ui.page-heading
            title="تماس"
            subtitle="برای همکاری، گفت‌وگو درباره‌ی یک مسئله یا پرسش درباره‌ی محصولات." />

        <div class="mt-12 grid gap-12 lg:grid-cols-5 lg:gap-16">

            {{-- ── کانال‌های مستقیم ── --}}
            <div class="lg:col-span-2">
                <h2 class="text-card font-semibold mb-5">راه‌های مستقیم</h2>

                @if ($channels->isEmpty())
                    <p class="text-small text-text-muted leading-relaxed">
                        اطلاعات تماس در حال تکمیل است. می‌توانید از فرم روبه‌رو پیام بگذارید.
                    </p>
                @else
                    <ul class="space-y-3">
                        @foreach ($channels as $channel)
                            <li class="card p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-small font-semibold mb-1">{{ $channel->label }}</p>
                                        <a href="{{ $channel->href() }}"
                                           @if ($channel->isExternal()) target="_blank" rel="noopener noreferrer" @endif
                                           class="text-small text-accent hover:text-accent-hover transition-colors break-all">
                                            {{ $channel->handle ?: $channel->url }}
                                            @if ($channel->isExternal())
                                                <span class="sr-only">(در پنجره‌ی جدید)</span>
                                            @endif
                                        </a>
                                    </div>

                                    {{-- دکمه‌ی کپی — برای ایمیل و آیدی مفید است --}}
                                    @if ($channel->channel_type === \App\Enums\ContactChannelType::Email)
                                        <button type="button"
                                                class="btn btn-ghost shrink-0"
                                                data-copy="{{ $channel->url }}"
                                                aria-label="کپی ایمیل">
                                            کپی
                                        </button>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- ── فرم تماس ── --}}
            <div class="lg:col-span-3">
                <h2 class="text-card font-semibold mb-5">ارسال پیام</h2>

                @if (session('contact_status') === 'sent')
                    <div class="mb-6">
                        <x-ui.alert variant="success" title="پیام دریافت شد">
                            پیام شما ثبت شد. در اسرع وقت پاسخ می‌دهم.
                        </x-ui.alert>
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="space-y-5" novalidate>
                    @csrf

                    {{--
                      Honeypot: فیلدی که کاربر واقعی نمی‌بیند ولی ربات
                      پر می‌کند. با `sr-only` پنهان است، نه `display:none`
                      — چون بعضی ربات‌ها فیلدهای display:none را رد می‌کنند.
                      `tabindex="-1"` و `autocomplete="off"` هم اضافه شده
                      تا کاربر صفحه‌کلید رویش نایستد.
                    --}}
                    <div class="sr-only" aria-hidden="true">
                        <label for="website">وب‌سایت</label>
                        <input type="text"
                               id="website"
                               name="website"
                               tabindex="-1"
                               autocomplete="off">
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="field-label">نام <span class="text-danger" aria-hidden="true">*</span></label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   autocomplete="name"
                                   class="field-input"
                                   @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                            @error('name')
                                <span id="name-error" class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="field-label">ایمیل <span class="text-danger" aria-hidden="true">*</span></label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   dir="ltr"
                                   autocomplete="email"
                                   class="field-input ltr"
                                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                            @error('email')
                                <span id="email-error" class="field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="purpose" class="field-label">موضوع</label>
                        <select id="purpose" name="purpose" class="field-input"
                                @error('purpose') aria-invalid="true" aria-describedby="purpose-error" @enderror>
                            <option value="">— انتخاب کنید —</option>
                            <option value="project" @selected(old('purpose') === 'project')>پروژه و همکاری فنی</option>
                            <option value="collaboration" @selected(old('purpose') === 'collaboration')>پیشنهاد همکاری</option>
                            <option value="question" @selected(old('purpose') === 'question')>پرسش</option>
                            <option value="service" @selected(old('purpose') === 'service')>درخواست خدمت</option>
                            <option value="other" @selected(old('purpose') === 'other')>سایر</option>
                        </select>
                        @error('purpose')
                            <span id="purpose-error" class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-baseline justify-between gap-3">
                            <label for="message" class="field-label">
                                پیام <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            {{-- شمارشگر با جاوااسکریپت پر می‌شود؛ اگر JS نباشد
                                 خالی می‌ماند و مشکلی نمی‌سازد. --}}
                            <span id="message-counter" class="text-label text-text-subtle num-fa" aria-live="polite"></span>
                        </div>
                        <textarea id="message"
                                  name="message"
                                  rows="7"
                                  required
                                  maxlength="5000"
                                  data-character-counter
                                  class="field-input resize-y"
                                  @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                        @error('message')
                            <span id="message-error" class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <div class="flex items-start gap-2.5">
                            <input type="checkbox"
                                   id="consent"
                                   name="consent"
                                   value="1"
                                   @checked(old('consent'))
                                   required
                                   class="mt-1 size-4 rounded border-border-strong text-accent"
                                   @error('consent') aria-invalid="true" aria-describedby="consent-error" @enderror>
                            <label for="consent" class="text-small text-text-muted leading-relaxed">
                                موافقم نام و ایمیل من برای پاسخ‌دادن ذخیره شود. این اطلاعات جای دیگری استفاده نمی‌شود.
                            </label>
                        </div>
                        @error('consent')
                            <span id="consent-error" class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        ارسال پیام
                        <x-ui.icon name="arrow-left" class="size-4" />
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
