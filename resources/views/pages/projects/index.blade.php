@extends('layouts.app')

{{-- فهرست پروژه‌ها — با حالت خالی محترمانه، نه صفحه‌ی سفید --}}

@section('content')
    <div class="container-page py-12 md:py-16">

        <x-ui.page-heading
            title="پروژه‌ها"
            subtitle="کارهایی که ساخته‌ام: مسئله، رویکرد، راه‌حل و آموخته‌ها." />

        @if ($projects->isEmpty())
            <div class="mt-12">
                <x-ui.empty-state
                    icon="cube"
                    title="هنوز پروژه‌ای ثبت نشده است"
                    description="پروژه‌ها به‌زودی از پنل مدیریت اضافه می‌شوند." />
            </div>
        @else
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    {{-- technologies برای نمایش چیپ‌ها از قبل لود می‌شود
                         تا روی هاست اشتراکی N+1 نسازیم. --}}
                    @php $project->loadMissing('technologies'); @endphp
                    <x-project-card :project="$project" :status-variant="$project->status->badgeClass()" />
                @endforeach
            </div>

            <div class="mt-12">
                <x-ui.pagination :paginator="$projects" />
            </div>
        @endif
    </div>
@endsection
