<footer class="relative z-10 mx-auto max-w-6xl px-4 pb-10 sm:px-6">
    <div class="card overflow-hidden p-8 sm:p-10">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr]">
            <div>
                <div class="flex items-center gap-2.5">
                    <x-ui.avatar :name="$siteName ?? 'حسین یاری'" :src="$siteAvatar ?? null" size="sm" class="h-10 w-10 rounded-xl text-lg" />
                    <div>
                        <p class="text-sm font-extrabold text-heading">{{ $siteName ?? 'حسین یاری' }}</p>
                        @if (! empty($siteHeadline))
                            <p class="text-[11px] text-muted">{{ $siteHeadline }}</p>
                        @endif
                    </div>
                </div>

                @if (! empty($siteBioShort))
                    <p class="mt-4 max-w-xs text-xs leading-6 text-body">{{ $siteBioShort }}</p>
                @endif

                @if (! empty($socialLinks) && $socialLinks->isNotEmpty())
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($socialLinks as $link)
                            <x-ui.icon-button
                                :icon="$link->icon"
                                :label="$link->label"
                                :href="$link->url"
                                size="sm"
                                target="_blank"
                                rel="noopener noreferrer me"
                            />
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <p class="text-[11px] font-bold text-muted">صفحات</p>
                <ul class="mt-4 space-y-2.5 text-[13px]">
                    <li><a href="{{ route('home') }}" class="crumb">خانه</a></li>
                    <li><a href="{{ route('platforms.index') }}" class="crumb">پلتفرم‌ها</a></li>
                    <li><a href="{{ route('resume.index') }}" class="crumb">رزومه</a></li>
                    @if ($blogEnabled ?? false)
                        <li><a href="{{ route('blog.index') }}" class="crumb">وبلاگ</a></li>
                    @endif
                    <li><a href="{{ route('contact.show') }}" class="crumb">تماس</a></li>
                </ul>
            </div>

            <div>
                <p class="text-[11px] font-bold text-muted">پلتفرم‌ها</p>
                @if (! empty($footerPlatforms) && $footerPlatforms->isNotEmpty())
                    <ul class="mt-4 space-y-2.5 text-[13px]">
                        @foreach ($footerPlatforms as $platform)
                            <li><a href="{{ route('platforms.show', $platform->slug) }}" class="crumb">{{ $platform->title }}</a></li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-xs leading-6 text-muted">به‌زودی اینجا فهرست می‌شوند.</p>
                @endif
            </div>
        </div>

        <div class="divider my-8"></div>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <p class="text-xs text-muted">© {{ fa_date(now(), 'y') }} {{ $siteName ?? 'حسین یاری' }} — همه‌ی حقوق محفوظ است.</p>
            <div class="flex items-center gap-3">
                @if ($blogEnabled ?? false)
                    <a href="{{ route('feed') }}" class="flex items-center gap-1.5 text-[11px] text-muted transition-colors hover:text-accent-deep dark:hover:text-accent">
                        <x-ui.icon name="rss" class="h-3.5 w-3.5" />
                        خوراک RSS
                    </a>
                @endif
                <a href="#top" class="icon-btn" aria-label="بازگشت به بالای صفحه">
                    <x-ui.icon name="arrow-up" class="h-[1.15em] w-[1.15em]" />
                </a>
            </div>
        </div>
    </div>
</footer>
