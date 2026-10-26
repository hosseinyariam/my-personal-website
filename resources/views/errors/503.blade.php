@extends('layouts.app')

{{--
  صفحه‌ی خطای ۵۰۳ — «سایت در حال به‌روزرسانی است».

  ── چرا layout کامل ──
  برخلاف ۴۱۹/۴۲۹/۵۰۰، این صفحه از layout اصلی استفاده می‌کند تا کاربر
  ناوبری و راه‌های ارتباطی را در دسترس داشته باشد. (۴۱۹/۴۲۹/۵۰۰ به
  HTML مستقل نیاز دارند چون در آن‌ها ممکن است نشست یا دیتابیس — و در
  نتیجه خودِ header — در دسترس نباشد.)
--}}

@section('content')
    <div class="container-page py-16 md:py-24">
        <div class="mx-auto max-w-xl text-center">
            <p class="text-hero font-bold text-accent num-fa" aria-hidden="true">۵۰۳</p>

            <h1 class="mt-4 text-page font-bold">سایت در حال به‌روزرسانی است</h1>

            <p class="mt-4 text-body text-text-muted leading-relaxed">
                در حال اعمال تغییرات هستیم و سایت چند لحظه‌ی دیگر برمی‌گردد. لطفاً کمی بعد دوباره سر بزنید.
            </p>

            <div class="mt-8">
                <a href="{{ route('home') }}" class="btn btn-primary">
                    بازگشت به صفحه اصلی
                    <x-ui.icon name="arrow-left" class="size-4" />
                </a>
            </div>
        </div>
    </div>
@endsection
