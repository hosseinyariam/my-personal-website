<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PublicationStatus;
use App\Models\Concerns\Publishable;
use App\Models\Module;
use App\Support\CacheKeys;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * سرویس انتشار محتوا — تنها مسیر تغییر وضعیت انتشار.
 *
 * ── چرا یک سرویس واحد ──
 * Blueprint:88-92 می‌گوید انتشار و لغو انتشار باید از **یک** مسیر
 * کنترل‌شده انجام شود. اگر هر Admin Page خودش `status` را ست کند،
 * سه چیز دیر یا زود فراموش می‌شود:
 *
 *   ۱. ست‌کردن `published_at` — بدون آن، scope عمومی محتوا را نمی‌بیند
 *      (چون fail-closed است) و مدیر فکر می‌کند «منتشر کردم ولی دیده نمی‌شود».
 *   ۲. باطل‌کردن کش — کاربر تا انقضای TTL نسخه‌ی کهنه می‌بیند.
 *   ۳. نرمال‌سازی زمان آینده — تاریخ آینده باید محتوا را زمان‌بندی‌شده کند،
 *      نه اینکه همین حالا منتشر کند.
 *
 * این سرویس هر سه را یک‌جا تضمین می‌کند.
 * ──────────────────────────────────────
 */
class PublishingService
{
    /**
     * انتشار فوری محتوا.
     */
    public function publish(Model $model, ?\DateTimeInterface $at = null): Model
    {
        $this->assertPublishable($model);

        return DB::transaction(function () use ($model, $at): Model {
            $attributes = [];

            if ($this->hasColumn($model, 'status')) {
                $attributes['status'] = PublicationStatus::Published->value;
            }

            if ($this->hasColumn($model, 'published_at')) {
                // اگر زمان آینده داده شده، همان می‌ماند — یعنی زمان‌بندی.
                // Scope عمومی تا رسیدن آن زمان محتوا را نشان نمی‌دهد.
                $attributes['published_at'] = $at ?? $model->getAttribute('published_at') ?? now();
            }

            $model->forceFill($attributes)->save();

            $this->forgetCaches();

            return $model->refresh();
        });
    }

    /**
     * بازگرداندن به پیش‌نویس.
     *
     * `published_at` عمداً پاک نمی‌شود: اگر مدیر دوباره منتشر کند،
     * تاریخ انتشار قبلی معنادار است. ولی چون `status` پیش‌نویس شده،
     * scope عمومی آن را نشان نمی‌دهد.
     */
    public function unpublish(Model $model): Model
    {
        $this->assertPublishable($model);

        return DB::transaction(function () use ($model): Model {
            if ($this->hasColumn($model, 'status')) {
                $model->forceFill(['status' => PublicationStatus::Draft->value])->save();
            } else {
                // مدل‌هایی که ستون status ندارند (Product/Project) با
                // پاک‌کردن published_at از دید عمومی خارج می‌شوند.
                $model->forceFill(['published_at' => null])->save();
            }

            $this->forgetCaches();

            return $model->refresh();
        });
    }

    /**
     * آرشیو — محتوا از مسیرهای عمومی حذف می‌شود ولی تاریخچه می‌ماند.
     * Blueprint:91: آرشیو حذف نیست.
     */
    public function archive(Model $model): Model
    {
        $this->assertPublishable($model);

        return DB::transaction(function () use ($model): Model {
            if ($this->hasColumn($model, 'status')) {
                $model->forceFill(['status' => PublicationStatus::Archived->value])->save();
            }

            $this->forgetCaches();

            return $model->refresh();
        });
    }

    /**
     * فهرست محتوایی که زمان انتشارش رسیده ولی هنوز پردازش نشده.
     *
     * برای فرمان زمان‌بند `contents:publish-scheduled`.
     *
     * @return \Illuminate\Support\Collection<int, Model>
     */
    public function dueForScheduledPublishing(string $modelClass): \Illuminate\Support\Collection
    {
        return $modelClass::query()
            ->where('status', PublicationStatus::Draft->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();
    }

    private function assertPublishable(Model $model): void
    {
        if (! in_array(Publishable::class, class_uses_recursive($model), true)) {
            throw new \InvalidArgumentException(
                'مدل '.$model::class.' از Concern انتشار استفاده نمی‌کند و قابل انتشار نیست.'
            );
        }
    }

    private function hasColumn(Model $model, string $column): bool
    {
        return array_key_exists($column, $model->getAttributes())
            || $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), $column);
    }

    /**
     * باطل‌کردن کش‌های وابسته به انتشار.
     *
     * فهرست صریح از `CacheKeys` می‌آید تا با افزودن کش جدید،
     * جای به‌روزرسانی معلوم باشد.
     */
    private function forgetCaches(): void
    {
        foreach (CacheKeys::invalidatedOnPublish() as $key) {
            Cache::forget($key);
        }

        // کش وضعیت ماژول‌ها هم چون ناوبری به آن‌ها وابسته است
        Cache::forget(CacheKeys::NAVIGATION);
    }

    /**
     * پیش‌فرض امن برای وضعیت انتشار یک رکورد جدید.
     *
     * هر محتوای تازه **پیش‌نویس** است. هیچ مسیری نباید محتوای جدید
     * را خودکار منتشر کند؛ این اصل «Human-in-the-loop» است (BIM:37-38).
     */
    public function defaultStatus(): PublicationStatus
    {
        return PublicationStatus::Draft;
    }

    /** آیا ماژول مربوط به این محتوا روشن است؟ */
    public function isModuleEnabled(string $moduleKey): bool
    {
        return Module::isEnabled($moduleKey);
    }
}
