@extends('layouts.app')

{{--
  فهرست محصولات.

  ── صداقت وضعیت ──
  هر کارت وضعیت واقعی عرضه را نشان می‌دهد (ایده/در حال توسعه/بتا/فعال).
  مخاطب فنی تفاوت بتا و فعال را می‌داند؛ برچسب صادقانه اعتماد می‌سازد.
--}}

@section('content')
    <div class="container-page py-12 md:py-16">

        <x-ui.page-heading
            title="محصولات"
            subtitle="محصولاتی که ساخته و عرضه کرده‌ام، با وضعیت واقعی هر کدام." />

        @if ($products->isEmpty())
            <div class="mt-12">
                <x-ui.empty-state
                    icon="cube"
                    title="هنوز محصولی ثبت نشده است"
                    description="محصولات به‌زودی از پنل مدیریت اضافه می‌شوند." />
            </div>
        @else
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <article class="card card-interactive h-full flex flex-col">
                        <div class="mb-3">
                            <x-ui.badge :variant="$product->status->badgeClass()">
                                {{ $product->status->label() }}
                            </x-ui.badge>
                        </div>

                        <h2 class="text-card font-semibold mb-2">
                            <a href="{{ route('products.show', $product) }}" class="hover:text-accent transition-colors">
                                {{ $product->name }}
                            </a>
                        </h2>

                        @if ($product->tagline)
                            <p class="text-small font-medium text-text-muted mb-2">{{ $product->tagline }}</p>
                        @endif

                        @if ($product->summary)
                            <p class="text-small text-text-muted leading-relaxed flex-1">{{ $product->summary }}</p>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="mt-12">
                <x-ui.pagination :paginator="$products" />
            </div>
        @endif
    </div>
@endsection
