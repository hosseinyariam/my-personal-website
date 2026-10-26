<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\PublicationStatus;
use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Builder;

/**
 * رفتار مشترک «محتوای قابل انتشار».
 *
 * ── تصمیم D17: یک مدل واحد برای انتشار ──────────────────────────
 * مستندات در این مورد متناقض بودند: ERD برای همه‌ی موجودیت‌ها
 * `publication_status` داشت، ولی Blueprint برای Product/Project وضعیت
 * دامنه (idea/live/completed) و برای Company/Note وضعیت انتشار
 * (draft/published) تعریف کرده بود.
 *
 * راه‌حل: سه قابلیت مستقل، هرکدام فقط اگر ستونش وجود داشته باشد:
 *
 *   ۱. `status`  — اگر معنایش «وضعیت انتشار» باشد، باید published باشد.
 *                  برای Product/Project معنایش چرخه‌ی دامنه است، پس
 *                  این قابلیت خاموش می‌شود.
 *   ۲. `published_at` — دروازه‌ی زمانی. تا نرسیدن زمان، دیده نمی‌شود.
 *   ۳. `visibility`   — اگر وجود داشته باشد، `private` را حذف می‌کند.
 *
 * قاعده‌ی طلایی: صفحات عمومی **هرگز** `where('status','published')`
 * دست‌نویس نمی‌نویسند. همیشه از این scopeها. اگر هر مدل خودش شرط
 * بنویسد، دیر یا زود یکی فراموش می‌کند `visibility` را بررسی کند و
 * محتوای خصوصی لو می‌رود.
 *
 * Fail-closed: محتوایی که `published_at` ندارد **منتشرشده نیست**،
 * حتی اگر status اش published باشد. پنل هنگام انتشار، تاریخ را
 * خودکار پر می‌کند تا این حالت پیش نیاید.
 * ────────────────────────────────────────────────────────────────
 */
trait Publishable
{
    /**
     * ستونی که وضعیت انتشار را نگه می‌دارد.
     *
     * `null` یعنی «این مدل وضعیت انتشار ندارد و انتشارش فقط با
     * published_at سنجیده می‌شود» — مورد Product و Project.
     */
    protected function publicationStatusColumn(): ?string
    {
        return 'status';
    }

    /** آیا این مدل ستون `visibility` دارد؟ */
    protected function hasVisibilityColumn(): bool
    {
        return true;
    }

    /** آیا این مدل ستون `published_at` دارد؟ */
    protected function hasPublishedAtColumn(): bool
    {
        return true;
    }

    /**
     * پایه‌ی همه‌ی Queryهای عمومی: منتشرشده و قابل دیدن.
     */
    public function scopePublished(Builder $query): Builder
    {
        if (($column = $this->publicationStatusColumn()) !== null) {
            $query->where($column, PublicationStatus::Published->value);
        }

        if ($this->hasPublishedAtColumn()) {
            $query->whereNotNull('published_at')
                ->where('published_at', '<=', now());
        }

        if ($this->hasVisibilityColumn()) {
            $query->where('visibility', '!=', Visibility::Private->value);
        }

        return $query;
    }

    /**
     * محتوایی که در فهرست‌ها، جست‌وجو و sitemap می‌آید.
     *
     * `unlisted` عمداً حذف می‌شود: با لینک مستقیم دیده می‌شود ولی
     * نباید در فهرست یا موتور جست‌وجو ظاهر شود.
     */
    public function scopeListable(Builder $query): Builder
    {
        $query->published();

        if ($this->hasVisibilityColumn()) {
            $query->where('visibility', Visibility::Public->value);
        }

        return $query;
    }

    /** فقط محتوای ویژگی‌دار برای بخش‌های صفحه اصلی. */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->listable()->where('is_featured', true);
    }

    /** آیا این رکورد همین حالا برای عموم قابل نمایش است؟ */
    public function isPubliclyVisible(): bool
    {
        return $this->isViewable();
    }

    /** آیا با لینک مستقیم قابل دیدن است (شامل unlisted)؟ */
    public function isViewable(): bool
    {
        if (($column = $this->publicationStatusColumn()) !== null) {
            if ($this->getAttribute($column) !== PublicationStatus::Published) {
                return false;
            }
        }

        if ($this->hasPublishedAtColumn()) {
            $publishedAt = $this->getAttribute('published_at');

            if ($publishedAt === null || $publishedAt->isFuture()) {
                return false;
            }
        }

        if ($this->hasVisibilityColumn()) {
            if ($this->getAttribute('visibility') === Visibility::Private) {
                return false;
            }
        }

        return true;
    }

    /** آیا در فهرست و sitemap می‌آید؟ */
    public function isListable(): bool
    {
        if (! $this->isViewable()) {
            return false;
        }

        if ($this->hasVisibilityColumn()) {
            return $this->getAttribute('visibility') === Visibility::Public;
        }

        return true;
    }
}
