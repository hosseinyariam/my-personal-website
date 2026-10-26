{{--
  بخش محصولات منتخب.

  ── تمایز محصول از پروژه در نمایش ──
  محصول «وضعیت عرضه» دارد نه «وضعیت ساخت». یک محصول ممکن است
  «آزمایشی» باشد — این برچسب صادقانه است و اعتماد می‌سازد، چون
  مخاطب فنی تفاوت بتا و فعال را می‌داند.
--}}
<section class="py-14 md:py-20 section-rule" aria-labelledby="featured-products-heading">
    <div class="container-page">
        <x-ui.section-heading
            :title="$title"
            :href="route('products.index')"
            id="featured-products-heading" />

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                <article class="card card-interactive h-full flex flex-col">
                    <div class="mb-3">
                        <x-ui.badge :variant="$product->status->badgeClass()">
                            {{ $product->status->label() }}
                        </x-ui.badge>
                    </div>

                    <h3 class="text-card font-semibold mb-2">
                        <a href="{{ route('products.show', $product) }}" class="hover:text-accent transition-colors">
                            {{ $product->name }}
                        </a>
                    </h3>

                    @if ($product->tagline)
                        <p class="text-small font-medium text-text-muted mb-2">{{ $product->tagline }}</p>
                    @endif

                    @if ($product->summary)
                        <p class="text-small text-text-muted leading-relaxed flex-1">{{ $product->summary }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
