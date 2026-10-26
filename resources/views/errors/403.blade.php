@extends('layouts.app')

{{--
  صفحه‌ی خطای ۴۰۳ — «دسترسی مجاز نیست».

  ── چرا layout کامل ──
  مثل ۴۰۴: خطای دسترسی معمولاً سرویس‌های پایه را از کار نمی‌اندازد،
  پس ناوبری در دسترس است و کاربر می‌تواند جای دیگری برود.
--}}

@section('content')
    <div class="container-page py-16 md:py-24">
        <div class="mx-auto max-w-xl text-center">
            <p class="text-hero font-bold text-accent num-fa" aria-hidden="true">۴۰۳</p>

            <h1 class="mt-4 text-page font-bold">دسترسی مجاز نیست</h1>

            <p class="mt-4 text-body text-text-muted leading-relaxed">
                برای مشاهده‌ی این صفحه دسترسی ندارید. اگر فکر می‌کنید اشتباهی رخ داده، از راه‌های تماس اطلاع دهید.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('home') }}" class="btn btn-primary">
                    بازگشت به صفحه اصلی
                    <x-ui.icon name="arrow-left" class="size-4" />
                </a>

                <a href="{{ route('contact.show') }}" class="btn btn-secondary">تماس با من</a>
            </div>
        </div>
    </div>
@endsection
