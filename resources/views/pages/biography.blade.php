@extends('layouts.app')

{{--
  بیوگرافی — «پرونده‌ی حرفه‌ای»، نه رزومه.

  ── ترتیب روایت ──
  از هویت (چه کسی هستم) به باور (چطور فکر می‌کنم) به شواهد (تجربه،
  مهارت، دستاورد، کار واقعی) و در پایان به دعوت به ارتباط. این ترتیب
  عمدی است: ادعا پیش از شاهد نمی‌آید.

  ── نمایش مشروط اجباری ──
  هر بخش فقط اگر داده دارد رندر می‌شود. هیچ عنوانی بدون بدنه چاپ
  نمی‌شود — «صفحات خالی» در مستندات پروژه صریحاً ممنوع شده است.
--}}

@section('content')
    <div class="container-page py-12 md:py-16">

        <x-ui.page-heading
            title="درباره من"
            :subtitle="$profile->short_bio ?: $profile->professional_title" />

        {{-- ── روایت حرفه‌ای ââ --}}}
        @if ($profile->biography)
            <section class="mt-12 max-w-prose" aria-labelledby="story-heading">
                <h2 id="story-heading" class="text-section font-bold mb-4">مسیر حرفه‌ای من</h2>
                {{-- متن چندخطی ساده است؛ nl2br آن را به پاراگراف تبدیل می‌کند
                     و e() از تزریق HTML جلوگیری می‌کند. --}}
                <div class="prose-fa text-text-muted">{!! nl2br(e($profile->biography)) !!}</div>
            </section>
        @endif

        {{-- ── باور و رویکرد ââ --}}}
        @if ($profile->philosophy)
            <section class="mt-12 max-w-prose" aria-labelledby="philosophy-heading">
                <h2 id="philosophy-heading" class="text-section font-bold mb-4">رویکرد و باورها</h2>
                <div class="prose-fa text-text-muted">{!! nl2br(e($profile->philosophy)) !!}</div>
            </section>
        @endif

        {{-- ── ارزش‌های کاری ââ --}}}
        @if (! empty($profile->core_values))
            <section class="mt-12 max-w-prose" aria-labelledby="values-heading">
                <h2 id="values-heading" class="text-section font-bold mb-4">ارزش‌های کاری</h2>
                <ul class="space-y-3">
                    @foreach ((array) $profile->core_values as $value)
                        {{-- مقدار می‌تواند رشته یا آرایه‌ی {title, ...} باشد؛
                             هر دو شکل پشتیبانی می‌شود. --}}
                        @php $label = is_array($value) ? ($value['title'] ?? '') : $value; @endphp

                        @if (filled($label))
                            <li class="flex items-start gap-2.5 text-body text-text-muted">
                                <x-ui.icon name="check" class="size-4 text-accent shrink-0 mt-1.5" />
                                <span>{{ $label }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- ── مأموریت فعلی ââ --}}}
        @if ($profile->current_mission)
            <section class="mt-12 max-w-prose" aria-labelledby="mission-heading">
                <div class="card bg-accent-soft border-0">
                    <h2 id="mission-heading" class="text-section font-bold mb-4">مأموریت فعلی</h2>
                    <div class="prose-fa text-text">{!! nl2br(e($profile->current_mission)) !!}</div>
                </div>
            </section>
        @endif

        {{-- ── تجربه‌های کاری ââ --}}}
        @if ($experiences->isNotEmpty())
            <section class="mt-14" aria-labelledby="experience-heading">
                <h2 id="experience-heading" class="text-section font-bold mb-6">تجربه‌ی کاری</h2>

                <div class="max-w-2xl">
                    <x-ui.timeline>
                        @foreach ($experiences as $experience)
                            @php
                                // نام شرکت در عنوان می‌آید و اگر صفحه‌اش قابل
                                // نمایش باشد، خودِ عنوان به آن لینک می‌شود —
                                // لینک شکسته ساخته نمی‌شود.
                                $company = $experience->company;
                                $isLinked = $company !== null && $company->isViewable();
                                $title = $company !== null
                                    ? $experience->title.' در '.$company->name
                                    : $experience->title;
                            @endphp

                            <x-ui.timeline-item
                                :date="$experience->periodLabel()"
                                :title="$title"
                                :description="$experience->description"
                                :href="$isLinked ? route('companies.show', $company) : null">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($experience->employment_type)
                                        <x-ui.badge variant="neutral">
                                            {{ $experience->employment_type->label() }}
                                        </x-ui.badge>
                                    @endif

                                    @if ($experience->location_label)
                                        <span class="text-label text-text-subtle">{{ $experience->location_label }}</span>
                                    @endif
                                </div>
                            </x-ui.timeline-item>
                        @endforeach
                    </x-ui.timeline>
                </div>
            </section>
        @endif

        {{-- ── تحصیلات ââ --}}}
        @if ($educations->isNotEmpty())
            <section class="mt-14" aria-labelledby="education-heading">
                <h2 id="education-heading" class="text-section font-bold mb-6">تحصیلات</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($educations as $education)
                        <article class="card">
                            <h3 class="text-card font-bold text-text">{{ $education->institution }}</h3>
                            <p class="mt-1 text-small text-text-muted">{{ $education->fullTitle() }}</p>

                            @if ($education->periodLabel() !== '')
                                <p class="mt-2 text-label text-text-subtle num-fa">{{ $education->periodLabel() }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── مهارت‌ها، گروه‌بندی‌شده بر اساس دسته ──
             این بخش فقط مهارت‌هایی را نشان می‌دهد که به پروژه‌ی عمومی
             وصل‌اند (scopeEvidenced در کنترلر). اصل «شاهد بر ادعا». --}}
        @if ($skills->isNotEmpty())
            <section class="mt-14" aria-labelledby="skills-heading">
                <h2 id="skills-heading" class="text-section font-bold mb-2">مهارت‌ها</h2>

                {{-- یادداشت صراحت: توضیح می‌دهد چرا فهرست کوتاه است. --}}
                <p class="mb-6 text-small text-text-muted max-w-prose">
                    این فهرست فقط مهارت‌هایی را نشان می‌دهد که در کار واقعی و پروژه‌های منتشرشده به‌کار رفته‌اند.
                    مهارت بدون شاهد، ادعاست — و اینجا ادعای بی‌شاهد نمایش داده نمی‌شود.
                </p>

                <div class="space-y-6">
                    @foreach ($skills as $category => $categorySkills)
                        <div>
                            {{-- دسته‌ی خالی (بدون category) زیر «سایر» می‌آید. --}}
                            <h3 class="text-small font-semibold mb-3 text-text-muted">
                                {{ filled($category) ? $category : 'سایر' }}
                            </h3>

                            <ul class="flex flex-wrap gap-1.5">
                                @foreach ($categorySkills as $skill)
                                    <li class="badge badge-accent">{{ $skill->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── فناوری‌ها ââ --}}}
        @if ($technologies->isNotEmpty())
            <section class="mt-14" aria-labelledby="technologies-heading">
                <h2 id="technologies-heading" class="text-section font-bold mb-6">فناوری‌ها</h2>

                <ul class="flex flex-wrap gap-1.5">
                    @foreach ($technologies as $technology)
                        <li class="badge badge-neutral">{{ $technology->name }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- ── دستاوردها ââ --}}}
        @if ($achievements->isNotEmpty())
            <section class="mt-14" aria-labelledby="achievements-heading">
                <h2 id="achievements-heading" class="text-section font-bold mb-6">دستاوردها</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($achievements as $achievement)
                        <article class="card">
                            <h3 class="text-card font-bold text-text">{{ $achievement->title }}</h3>

                            @if ($achievement->description)
                                <p class="mt-2 text-small text-text-muted leading-relaxed">{{ $achievement->description }}</p>
                            @endif

                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                @if ($achievement->dateLabel() !== '')
                                    <span class="text-label text-text-subtle num-fa">{{ $achievement->dateLabel() }}</span>
                                @endif

                                @if ($achievement->evidence_url)
                                    <a href="{{ $achievement->evidence_url }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="text-small text-accent hover:text-accent-hover transition-colors inline-flex items-center gap-1.5">
                                        مشاهده‌ی شاهد
                                        <x-ui.icon name="external" class="size-4" />
                                        <span class="sr-only">(در پنجره‌ی جدید)</span>
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── گواهی‌نامه‌ها ââ --}}}
        @if ($certificates->isNotEmpty())
            <section class="mt-14" aria-labelledby="certificates-heading">
                <h2 id="certificates-heading" class="text-section font-bold mb-6">گواهی‌نامه‌ها</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($certificates as $certificate)
                        <article class="card">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-card font-bold text-text">{{ $certificate->title }}</h3>

                                {{-- گواهی منقضی هرگز «معتبر» جا زده نمی‌شود؛
                                     برچسب خنثی صراحتاً وضعیت را می‌گوید. --}}
                                @if ($certificate->isExpired())
                                    <x-ui.badge variant="neutral">منقضی</x-ui.badge>
                                @endif
                            </div>

                            <p class="mt-1 text-small text-text-muted">{{ $certificate->issuer }}</p>

                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                @if ($certificate->periodLabel() !== '')
                                    <span class="text-label text-text-subtle num-fa">{{ $certificate->periodLabel() }}</span>
                                @endif

                                @if ($certificate->credential_url)
                                    <a href="{{ $certificate->credential_url }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="text-small text-accent hover:text-accent-hover transition-colors inline-flex items-center gap-1.5">
                                        مشاهده‌ی گواهی
                                        <x-ui.icon name="external" class="size-4" />
                                        <span class="sr-only">(در پنجره‌ی جدید)</span>
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── کارهای منتخب — حلقه‌ی کشف از هویت به شاهد ââ --}}}
        @if ($projects->isNotEmpty())
            <section class="mt-14" aria-labelledby="projects-heading">
                <div class="flex items-baseline justify-between gap-4">
                    <h2 id="projects-heading" class="text-section font-bold">کارهای منتخب</h2>

                    <a href="{{ route('projects.index') }}"
                       class="ms-auto shrink-0 inline-flex min-h-11 items-center gap-1 rounded-control px-2 text-small font-medium text-accent hover:text-accent-hover transition-colors">
                        همه‌ی پروژه‌ها
                        <x-ui.icon name="arrow-left" class="size-4" />
                    </a>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        @php $project->loadMissing('technologies'); @endphp
                        <x-project-card :project="$project" :status-variant="$project->status->badgeClass()" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── دانلود رزومه — فقط اگر فایل واقعی آپلود شده باشد ââ --}}}
        @if ($profile->hasResume())
            <section class="mt-14" aria-labelledby="resume-heading">
                <div class="card flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 id="resume-heading" class="text-card font-bold text-text">رزومه‌ی کامل</h2>
                        <p class="mt-1 text-small text-text-muted">نسخه‌ی PDF رزومه برای دانلود در دسترس است.</p>
                    </div>

                    <a href="{{ route('biography.resume') }}" class="btn btn-primary shrink-0">
                        دانلود رزومه
                        <x-ui.icon name="arrow-left" class="size-4" />
                    </a>
                </div>
            </section>
        @endif

        {{-- ── دعوت به ارتباط ââ --}}}
        <section class="mt-16 pt-8 section-rule" aria-labelledby="biography-contact-heading">
            <div class="max-w-2xl">
                <h2 id="biography-contact-heading" class="text-section font-bold mb-3">گفت‌وگو کنیم</h2>
                <p class="text-body text-text-muted leading-relaxed mb-6">
                    برای همکاری، گفت‌وگو درباره‌ی یک مسئله یا پرسشی درباره‌ی کارهایم، راه‌های ارتباطی باز است.
                </p>

                <a href="{{ route('contact.show') }}" class="btn btn-primary">
                    تماس با من
                    <x-ui.icon name="arrow-left" class="size-4" />
                </a>
            </div>
        </section>
    </div>
@endsection
