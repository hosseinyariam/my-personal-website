@extends('layouts.app')

{{--
  صفحه‌ی خطای ۴۰۴ — «صفحه پیدا نشد».

  ── چرا layout کامل ──
  خطای ۴۰۴ معمولاً یعنی مسیر وجود ندارد، نه اینکه سرویس‌های پایه از
  کار افتاده باشند. پس header و ناوبری در دسترس‌اند و کاربر می‌تواند
  از همان صفحه به جای دیگری برود. (برخلاف ۴۱۹/۴۲۹/۵۰۰ که در آن‌ها
  ممکن است نشست یا دیتابیس در دسترس نباشد و به HTML مستقل نیاز دارند.)

  ── چرا شماره‌ی بزرگ ──
  شماره‌ی وضعیت، معنای فنی را سریع منتقل می‌کند و به‌عنوان عنصر
  تزئینی aria-hidden است؛ معنای واقعی در عنوان و متن می‌آید.
--}}

@section('content')
    <div class="container-page py-16 md:py-24">
        <div class="mx-auto max-w-xl text-center">
            <p class="text-hero font-bold text-accent num-fa" aria-hidden="true">۴۰۴</p>

            <h1 class="mt-4 text-page font-bold">صفحه پیدا نشد</h1>

            <p class="mt-4 text-body text-text-muted leading-relaxed">
                صفحه‌ای که دنبالش بودید وجود ندارد یا حذف شده است. ممکن است نشانی تغییر کرده باشد.
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
