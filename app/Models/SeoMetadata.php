<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * فراداده‌ی SEO یک موجودیت.
 *
 * نکته‌ی مهم معماری:305: این رابطه چندریختی است و MySQL نمی‌تواند کلید
 * خارجی برایش بسازد. پس سلامت داده در Observer تضمین می‌شود: با حذف
 * موجودیت، ردیف SEO یتیم نمی‌ماند.
 *
 * نبود این ردیف یک حالت معتبر است، نه خطا. نمایش عمومی به عنوان و
 * خلاصه‌ی خودِ محتوا بازمی‌گردد (`HasSeo::seoTitle`).
 */
class SeoMetadata extends Model
{
    protected $table = 'seo_metadata';

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'meta_title',
        'meta_description',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image_media_id',
    ];

    /** @return MorphTo<Model, $this> */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<Media, $this> */
    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_media_id');
    }

    /**
     * مقدار مؤثر `robots` — پیش‌فرض منطقی اگر مدیر چیزی نگفته باشد.
     *
     * محتوای `unlisted` باید `noindex` بگیرد تا از فهرست موتور جست‌وجو
     * بیرون بماند، حتی اگر منتشر شده باشد.
     */
    public function effectiveRobots(): string
    {
        if (filled($this->robots)) {
            return (string) $this->robots;
        }

        $seoable = $this->seoable;

        if ($seoable !== null && method_exists($seoable, 'isViewable') && ! $seoable->isViewable()) {
            return 'noindex, nofollow';
        }

        return 'index, follow';
    }
}
