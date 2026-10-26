@extends('layouts.app')

{{--
  فهرست شرکت‌ها.

  ── شمارش با ارقام فارسی ──
  تعداد محصول و پروژه با `withCount` از کنترلر می‌آید؛ اینجا فقط
  قالب‌بندی می‌شود. شمارش صفر نمایش داده نمی‌شود تا کارت شلوغ نشود.
--}}

@section('content')
    <div class="container-page py-12 md:py-16">

        <x-ui.page-heading
            title="شرکت‌ها"
            subtitle="شرکت‌ها و تیم‌هایی که در آن‌ها نقش داشته‌ام و محصولاتی که ساخته‌ایم." />

        @if ($companies->isEmpty())
            <div class="mt-12">
                <x-ui.empty-state
                    icon="building"
                    title="هنوز شرکتی ثبت نشده است"
                    description="شرکت‌ها به‌زودی از پنل مدیریت اضافه می‌شوند." />
            </div>
        @else
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($companies as $company)
                    <article class="card card-interactive h-full flex flex-col">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            @if ($company->relationship_type)
                                <x-ui.badge variant="accent">
                                    {{ $company->relationship_type->label() }}
                                </x-ui.badge>
                            @endif

                            {{-- نشانگر رابطه‌ی باز: متن صریح دارد، پس رنگ
                                 تنها حامل معنا نیست. --}}
                            @if ($company->isOngoing())
                                <x-ui.badge variant="success" :icon="true">فعال</x-ui.badge>
                            @endif
                        </div>

                        <h2 class="text-card font-semibold mb-2">
                            <a href="{{ route('companies.show', $company) }}" class="hover:text-accent transition-colors">
                                {{ $company->name }}
                            </a>
                        </h2>

                        @if ($company->summary)
                            <p class="text-small text-text-muted leading-relaxed flex-1">{{ $company->summary }}</p>
                        @endif

                        @php
                            // فقط شمارش‌های غیرصفر نمایش داده می‌شوند و با «·» جدا می‌شوند.
                            $counts = collect([
                                $company->products_count ? \App\Support\Persian::digits($company->products_count).' محصول' : null,
                                $company->projects_count ? \App\Support\Persian::digits($company->projects_count).' پروژه' : null,
                            ])->filter();
                        @endphp

                        @if ($counts->isNotEmpty())
                            <p class="mt-4 text-label text-text-subtle num-fa">{{ $counts->implode(' · ') }}</p>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="mt-12">
                <x-ui.pagination :paginator="$companies" />
            </div>
        @endif
    </div>
@endsection
