<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CurrentFocusStatus;
use App\Enums\CurrentFocusType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * تمرکز فعلی — «الان روی چه چیزی کار می‌کنم؟»
 *
 * این موجودیت همان چیزی است که IA:604 «Living Profile» را می‌سازد.
 * بدون آن، صفحه‌ی اصلی یک رزومه‌ی ایستا است.
 *
 * تصمیم D6: سه کلید خارجی خالی، با اعتبارسنجی «حداکثر یکی».
 * انعطاف در زمان نوشتن، سخت‌گیری در زمان ذخیره.
 */
class CurrentFocus extends Model
{
    /** @use HasFactory<\Database\Factories\CurrentFocusFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'focus_type',
        'status',
        'started_at',
        'target_date',
        'project_id',
        'product_id',
        'company_id',
        'is_featured',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'focus_type' => CurrentFocusType::class,
            'status' => CurrentFocusStatus::class,
            'started_at' => 'date',
            'target_date' => 'date',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CurrentFocusStatus::Active->value);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('started_at');
    }

    /** آیا بیش از یک هدف مرتبط دارد؟ برای هشدار در پنل. */
    public function hasMultipleTargets(): bool
    {
        return collect([$this->project_id, $this->product_id, $this->company_id])
            ->filter()
            ->count() > 1;
    }

    /** موجودیت مرتبط، هر کدام که ست شده باشد. */
    public function relatedEntity(): ?Model
    {
        return $this->project ?? $this->product ?? $this->company;
    }

    /** لینک عمومی به موجودیت مرتبط، اگر قابل نمایش باشد. */
    public function relatedUrl(): ?string
    {
        $related = $this->relatedEntity();

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
            default => null,
        };
    }
}
