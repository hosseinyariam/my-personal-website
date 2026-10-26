<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * فراداده‌ی SEO برای هر موجودیت عمومی.
 *
 * یک رابطه‌ی چندریختی با یگانگی ترکیبی. ردیف SEO فقط زمانی ساخته می‌شود
 * که مدیر واقعاً مقداری وارد کند؛ نبود آن یعنی «خودکار از عنوان و خلاصه».
 */
trait HasSeo
{
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'seoable');
    }

    /**
     * عنوان SEO مؤثر — با بازگشت به عنوان خودِ محتوا.
     *
     * چرا بازگشت در مدل و نه در Blade: قاعده‌ی «صفر تکرار منطق» را
     * رعایت می‌کند و تست می‌تواند رفتار را مستقیماً بسنجد.
     */
    public function seoTitle(): string
    {
        return $this->seo?->meta_title
            ?: (string) ($this->title ?? $this->name ?? '');
    }

    public function seoDescription(): ?string
    {
        return $this->seo?->meta_description
            ?: ($this->summary ?? $this->excerpt ?? null);
    }

    public function canonicalUrl(): ?string
    {
        return $this->seo?->canonical_url;
    }

    /** آیا فراداده‌ی SEO ناقص است؟ برای هشدار در پنل. */
    public function isSeoIncomplete(): bool
    {
        return blank($this->seo?->meta_description);
    }

    public function scopeWhereSeoIncomplete(Builder $query): Builder
    {
        return $query->whereDoesntHave('seo', fn (Builder $q) => $q->whereNotNull('meta_description'));
    }
}
