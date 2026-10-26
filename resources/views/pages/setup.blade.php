@extends('layouts.app')

{{--
  صفحه‌ی راه‌اندازی — وقتی هنوز پروفایل ثبت نشده است.

  ── چرا این صفحه وجود دارد ──
  سایت تازه دیپلوی می‌شود و ممکن است چند ساعت تا ورود محتوا فاصله
  باشد. دو گزینه بد وجود دارد:
    ۱. خطای ۵۰۰ — سایت شکسته به نظر می‌رسد
    ۲. محتوای ساختگی — نقض صریح «هیچ محتوای ساختگی» (دستورالعمل §۲)

  این صفحه گزینه‌ی سوم است: صادقانه می‌گوید سایت در حال آماده‌سازی
  است، بدون ادعا و بدون شکستن.

  ── noindex ──
  این صفحه در موتور جست‌وجو نباید ایندکس شود — محتوایی برای
  ایندکس ندارد. noindex از HomeController ست می‌شود.
--}}

@section('content')
    <div class="container-page py-24 md:py-32">
        <div class="max-w-lg mx-auto text-center">
            <div class="grid size-14 mx-auto place-items-center rounded-card bg-accent-soft text-accent mb-6">
                <x-ui.icon name="sparkle" class="size-7" />
            </div>

            <h1 class="text-page font-bold mb-4">در حال آماده‌سازی</h1>

            <p class="text-body text-text-muted leading-relaxed">
                این سایت در حال راه‌اندازی است. محتوای اصلی به‌زودی از طریق پنل مدیریت وارد می‌شود.
            </p>

            <p class="mt-6 text-small text-text-subtle">
                اگر مدیر این سایت هستید، از مسیر
                <a href="/admin" class="text-accent hover:text-accent-hover underline">پنل مدیریت</a>
                محتوا را وارد کنید.
            </p>
        </div>
    </div>
@endsection
