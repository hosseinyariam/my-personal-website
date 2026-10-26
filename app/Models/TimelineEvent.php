<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Enums\TimelineEventType;
use App\Enums\Visibility;
use App\Models\Concerns\Publishable;
use App\Support\Persian;
use Database\Factories\TimelineEventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * رویداد مسیر حرفه‌ای.
 *
 * تصمیم D5: رابطه‌ی چندریختی تک‌هدف. هر رویداد دقیقاً به یک موجودیت
 * اصلی وصل می‌شود (پروژه، محصول، شرکت، …). این از چهار کلید خارجی
 * خالی جلوگیری می‌کند.
 *
 * اصل IA:234: Timeline باید «Curated» باشد. سیستم می‌تواند هزار رویداد
 * ذخیره کند، ولی فقط `is_highlight`ها در سایت عمومی به‌عنوان نقطه‌ی
 * عطف نمایش داده می‌شوند. اینطور مسیر، روایت‌محور می‌ماند نه لاگ.
 */
class TimelineEvent extends Model
{
    /** @use HasFactory<TimelineEventFactory> */
    use HasFactory;

    use Publishable;

    protected $fillable = [
        'title',
        'summary',
        'event_type',
        'occurred_at',
        'ended_at',
        'timelineable_type',
        'timelineable_id',
        'status',
        'visibility',
        'published_at',
        'is_highlight',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'event_type' => TimelineEventType::class,
            'status' => PublicationStatus::class,
            'visibility' => Visibility::class,
            'occurred_at' => 'date',
            'ended_at' => 'date',
            'published_at' => 'datetime',
            'is_highlight' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function timelineable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_highlight')
            ->orderBy('sort_order')
            ->orderByDesc('occurred_at');
    }

    public function scopeHighlights(Builder $query): Builder
    {
        return $query->listable()->where('is_highlight', true);
    }

    /** سال رویداد برای گروه‌بندی روی خط زمان. */
    public function year(): ?int
    {
        return $this->occurred_at?->year;
    }

    public function dateLabel(): string
    {
        return Persian::date($this->occurred_at, 'MMMM y');
    }

    /** لینک به موجودیت مرتبط، اگر موجود باشد و قابل نمایش باشد. */
    public function relatedUrl(): ?string
    {
        $related = $this->timelineable;

        if ($related === null) {
            return null;
        }

        if (method_exists($related, 'isViewable') && ! $related->isViewable()) {
            return null;
        }

        return match (true) {
            $related instanceof Project => route('projects.show', $related),
            $related instanceof Product => route('products.show', $related),
            $related instanceof Company => route('companies.show', $related),
            $related instanceof Note => route('notes.show', $related),
            default => null,
        };
    }
}
