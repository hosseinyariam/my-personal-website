<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Concerns\HasSeo;
use App\Models\MediaAttachment;
use App\Models\SeoMetadata;
use App\Support\CacheKeys;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * پاک‌سازی ردیف‌های وابسته‌ی چندریختی هنگام حذف موجودیت.
 *
 * ── چرا این Observer لازم است ──
 * معمارى:305 صریح می‌گوید رابطه‌های چندریختی نمی‌توانند کلید خارجی
 * واقعی در MySQL داشته باشند، پس مرجع یتیم ممکن است. بدون این
 * Observer، حذف یک پروژه ردیف `seo_metadata` و `media_attachments`
 * را تنها می‌گذاشت و این‌ها به‌صورت زباله در دیتابیس می‌ماندند.
 *
 * اما نکته‌ی مهم: حذف پیوست رسانه، **فایل فیزیکی را حذف نمی‌کند**.
 * چون یک فایل ممکن است جای دیگری هم استفاده شده باشد (CMS:185-188).
 * حذف فایل فیزیکی فقط از طریق `MediaService` و پس از بررسی انجام می‌شود.
 */
class PurgeMorphRelationsObserver
{
    public function deleting(Model $model): void
    {
        // ردیف SEO این موجودیت
        if (in_array(HasSeo::class, class_uses_recursive($model), true)) {
            SeoMetadata::query()
                ->where('seoable_type', $model->getMorphClass())
                ->where('seoable_id', $model->getKey())
                ->delete();
        }

        // پیوست‌های رسانه — فقط ردیف پیوست، نه فایل
        MediaAttachment::query()
            ->where('mediable_type', $model->getMorphClass())
            ->where('mediable_id', $model->getKey())
            ->delete();
    }

    public function saved(Model $model): void
    {
        $this->forgetCaches();
    }

    public function deleted(Model $model): void
    {
        $this->forgetCaches();
    }

    /**
     * با هر تغییر محتوا، کش‌های وابسته باطل می‌شوند.
     *
     * معماری:1006 باطل‌کردن کش پس از انتشار/ویرایش را الزام کرده است.
     * بدون این، کاربر تا انقضای TTL، نسخه‌ی کهنه می‌بیند.
     */
    private function forgetCaches(): void
    {
        foreach (CacheKeys::invalidatedOnPublish() as $key) {
            Cache::forget($key);
        }
    }
}
