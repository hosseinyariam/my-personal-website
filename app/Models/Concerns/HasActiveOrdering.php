<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * الگوی عمومی همه‌ی موجودیت‌های محتوایی: is_active + sort_order.
 * همین دو ستون است که «افزودن رکورد بدون کد» را ممکن می‌کند (G4).
 */
trait HasActiveOrdering
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
