@extends('layouts.app')

{{--
  مسیر حرفه‌ای — خط زمان گروه‌بندی‌شده بر اساس سال.

  ── چرا گروه‌بندی سال‌محور ──
  IA:238-242 نمونه‌ای سال‌محور می‌دهد. گروه‌بندی بصری، مسیر را قابل
  اسکن می‌کند: کاربر ابتدا ساختار کلی سال‌ها را می‌بیند، بعد جزئیات.

  ── کلید 'undated' ──
  رویداد بدون تاریخ در گروهی با برچسب «بدون تاریخ» می‌رود، نه در یک
  سال جعلی. ساختن سال برای رویدادی که تاریخ ندارد، داده‌ی نادرست است.

  ── ترتیب ──
  کنترلر کلیدها را نزولی مرتب کرده (`sortKeysDesc`). سال جدیدتر اول.
  کلید 'undated' چون رشته‌ی غیرعددی است در این مرتب‌سازی به انتها
  می‌رود که رفتار مطلوب است.
--}}

@section('content')
    <div class="container-page py-12 md:py-16">

        <x-ui.page-heading
            title="مسیر حرفه‌ای"
            subtitle="نقاط عطف مسیر کاری: از طراحی وب و فریلنسری تا ساخت محصول و کارآفرینی فناوری." />

        @if ($events->isEmpty())
            <div class="mt-12">
                <x-ui.empty-state
                    icon="route"
                    title="هنوز رویدادی ثبت نشده است"
                    description="نقاط عطف مسیر به‌زودی از پنل مدیریت اضافه می‌شوند." />
            </div>
        @else
            <div class="mt-10 space-y-12">
                @foreach ($eventsByYear as $year => $yearEvents)
                    <section aria-labelledby="year-{{ $year }}-heading">
                        <h2 id="year-{{ $year }}-heading" class="text-section font-bold mb-6 num-fa">
                            {{ $year === 'undated' ? 'بدون تاریخ' : \App\Support\Persian::digits($year) }}
                        </h2>

                        <div class="max-w-2xl">
                            <x-ui.timeline>
                                @foreach ($yearEvents as $event)
                                    <x-ui.timeline-item
                                        :date="$event->dateLabel()"
                                        :title="$event->title"
                                        :description="$event->summary"
                                        :href="$event->relatedUrl()"
                                        :highlight="$event->is_highlight">
                                        @if ($event->event_type)
                                            <x-ui.badge variant="neutral">{{ $event->event_type->label() }}</x-ui.badge>
                                        @endif
                                    </x-ui.timeline-item>
                                @endforeach
                            </x-ui.timeline>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
@endsection
