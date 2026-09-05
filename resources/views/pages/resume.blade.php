<x-layouts.app title="رزومه" description="سوابق کاری، مهارت‌ها، تحصیلات و گواهی‌ها.">
    <div class="space-y-5">
        <div data-reveal>
            <x-ui.breadcrumb :items="[
                ['label' => 'خانه', 'url' => route('home')],
                ['label' => 'رزومه'],
            ]" />
        </div>

        @if ($experiences->isEmpty() && $educations->isEmpty() && $skillGroups->isEmpty() && $certificates->isEmpty() && ! $hasResumePdf)
            <x-ui.card data-reveal>
                <x-ui.empty-state
                    icon="file-cv"
                    level="h1"
                    title="رزومه هنوز تکمیل نشده است"
                    action="تماس مستقیم"
                    :href="route('contact.show')"
                >
                    به‌زودی سوابق و مهارت‌ها همین‌جا منتشر می‌شود. تا آن زمان می‌توانید مستقیم در تماس باشید.
                </x-ui.empty-state>
            </x-ui.card>
        @else
            <div class="card p-7 sm:p-9" data-reveal>
                <div class="flex flex-wrap items-start justify-between gap-5">
                    <div class="max-w-xl">
                        <h1 class="text-h1 text-heading">{{ $siteName }}</h1>
                        @if ($siteHeadline)
                            <p class="mt-2 text-sm font-bold text-accent-deep dark:text-accent">{{ $siteHeadline }}</p>
                        @endif
                        @if ($bioLong ?: $siteBioShort)
                            <p class="mt-4 text-sm leading-8 text-body">{{ $bioLong ?: $siteBioShort }}</p>
                        @endif
                    </div>

                    {{-- دکمه فقط با وجود فایل آپلودشده (US-M4-2) --}}
                    @if ($hasResumePdf)
                        <x-ui.button
                            :href="route('resume.download')"
                            variant="primary"
                            size="md"
                            icon="download"
                            data-track="resume_download"
                        >دانلود رزومه</x-ui.button>
                    @endif
                </div>

                <div class="divider my-8"></div>

                <div class="grid gap-10 lg:grid-cols-[1fr_300px]">
                    {{-- ── تجربه‌ی کاری در تایم‌لاین ─────────── --}}
                    <div>
                        @if ($experiences->isNotEmpty())
                            <p class="kicker">تجربه‌ی کاری</p>
                            <x-ui.timeline class="mt-6">
                                @foreach ($experiences as $experience)
                                    <x-ui.timeline-item
                                        :title="$experience->role"
                                        :badge="$experience->company"
                                        :period="$experience->period()"
                                    >
                                        @if ($lines = $experience->achievementLines())
                                            <ul class="mt-3 space-y-2 text-sm leading-7 text-body">
                                                @foreach ($lines as $line)
                                                    <li class="flex gap-2.5">
                                                        <x-ui.icon name="point-filled" class="mt-1.5 h-3 w-3 shrink-0 text-accent-deep dark:text-accent" />
                                                        <span>{{ $line }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </x-ui.timeline-item>
                                @endforeach
                            </x-ui.timeline>
                        @else
                            <x-ui.alert variant="line" title="سوابق کاری هنوز وارد نشده است">
                                این بخش به‌محض ورود اولین سابقه، همین‌جا نمایش داده می‌شود.
                            </x-ui.alert>
                        @endif
                    </div>

                    <div class="space-y-5">
                        {{-- ── مهارت‌ها به‌تفکیک دسته ──────────── --}}
                        @if ($skillGroups->isNotEmpty())
                            <div class="panel p-5">
                                <p class="kicker">مهارت‌ها</p>
                                <div class="mt-4 space-y-4">
                                    @foreach ($skillGroups as $skills)
                                        <div>
                                            <p class="mb-2 text-[11px] font-bold text-muted">{{ $skills->first()->category->label() }}</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($skills as $skill)
                                                    <x-ui.chip>{{ $skill->name }}</x-ui.chip>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- ── تحصیلات و گواهی‌ها ──────────────── --}}
                        @if ($educations->isNotEmpty() || $certificates->isNotEmpty())
                            <div class="panel p-5">
                                <p class="kicker">تحصیلات و گواهی‌ها</p>
                                <div class="mt-4 space-y-4">
                                    @foreach ($educations as $education)
                                        <div class="flex items-start gap-3">
                                            <x-ui.tile icon="school" variant="line" size="sm" class="h-10 w-10 rounded-xl" />
                                            <div class="min-w-0">
                                                <p class="text-[13px] font-bold text-heading">{{ $education->degree }}@if ($education->field) — {{ $education->field }}@endif</p>
                                                <p class="mt-0.5 text-[11px] text-muted">{{ $education->institution }}</p>
                                                <p class="mt-0.5 font-mono text-[10px] text-muted" dir="ltr">{{ $education->period() }}</p>
                                            </div>
                                        </div>
                                    @endforeach

                                    @foreach ($certificates as $certificate)
                                        <div class="flex items-start gap-3">
                                            <x-ui.tile icon="award" variant="line" size="sm" class="h-10 w-10 rounded-xl" />
                                            <div class="min-w-0">
                                                @if ($certificate->url)
                                                    <a href="{{ $certificate->url }}" target="_blank" rel="noopener noreferrer"
                                                       class="text-[13px] font-bold text-heading transition-colors hover:text-accent-deep dark:hover:text-accent">{{ $certificate->title }}</a>
                                                @else
                                                    <p class="text-[13px] font-bold text-heading">{{ $certificate->title }}</p>
                                                @endif
                                                <p class="mt-0.5 text-[11px] text-muted">{{ $certificate->issuer }}</p>
                                                <p class="mt-0.5 font-mono text-[10px] text-muted" dir="ltr">{{ fa_digits($certificate->year) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
