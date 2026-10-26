@extends('layouts.app')

{{--
  صفحه اصلی — «Living Profile».

  ── منطق ساخت ──
  بخش‌ها از `HomepageService` می‌آیند و بخشی که محتوا ندارد
  اصلاً در آرایه نیست. پس این فایل فقط حلقه می‌زند و رندر می‌کند —
  هیچ شرط «آیا محتوا هست؟» اینجا نیست. دلیل: منطق در سرویس متمرکز
  است و نما ساده می‌ماند.

  ── ترتیب بخش‌ها ──
  ترتیب از جدول `homepage_sections` می‌آید و از پنل قابل تغییر است.
  این فایل ترتیب را تحمیل نمی‌کند، فقط پیروی می‌کند.
--}}

@section('content')

    @foreach ($sections as $section)
        @switch($section['key'])

            @case('hero')
                @include('pages.partials.hero', ['profile' => $section['profile']])
                @break

            @case('current_focus')
                @include('pages.partials.current-focus', ['title' => $section['title'], 'focuses' => $section['focuses']])
                @break

            @case('featured_projects')
                @include('pages.partials.featured-projects', ['title' => $section['title'], 'projects' => $section['projects']])
                @break

            @case('featured_products')
                @include('pages.partials.featured-products', ['title' => $section['title'], 'products' => $section['products']])
                @break

            @case('selected_notes')
                @include('pages.partials.selected-notes', ['title' => $section['title'], 'notes' => $section['notes']])
                @break

            @case('journey_preview')
                @include('pages.partials.journey-preview', ['title' => $section['title'], 'events' => $section['events']])
                @break

            @case('biography_preview')
                @include('pages.partials.biography-preview', ['title' => $section['title'], 'profile' => $section['profile']])
                @break

            @case('how_i_think')
                @include('pages.partials.how-i-think', ['title' => $section['title'], 'philosophy' => $section['philosophy']])
                @break

            @case('contact_cta')
                @include('pages.partials.contact-cta', ['title' => $section['title'], 'channels' => $section['channels']])
                @break

        @endswitch
    @endforeach

@endsection
