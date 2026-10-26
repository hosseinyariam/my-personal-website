<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductStatus;
use App\Enums\Visibility;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\Publishable;
use App\Support\Slugger;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * محصول — چیزی که ساخته و عرضه شده.
 *
 * تفاوت با پروژه: محصول چرخه‌ی «عرضه» دارد (ایده → توسعه → بتا → فعال).
 * محصول می‌تواند چند پروژه داشته باشد و از چند پروژه ساخته شده باشد.
 */
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasMedia;
    use HasSeo;
    use Publishable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'summary',
        'description',
        'problem',
        'solution',
        'current_state',
        'website_url',
        'repository_url',
        'status',
        'started_at',
        'launched_at',
        'published_at',
        'is_featured',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'started_at' => 'date',
            'launched_at' => 'date',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * `status` چرخه‌ی عرضه‌ی محصول است (ایده/بتا/فعال)، نه وضعیت انتشار.
     * یک محصول «فعال» لزوماً نباید روی سایت نمایش داده شود؛ انتشار
     * فقط با `published_at` تعیین می‌شود.
     */
    protected function publicationStatusColumn(): ?string
    {
        return null;
    }

    /** جدول محصول ستون `visibility` ندارد؛ انتشارش با `published_at` است. */
    protected function hasVisibilityColumn(): bool
    {
        return false;
    }

    protected static function booted(): void
    {
        static::saving(function (self $product): void {
            if (blank($product->slug)) {
                $product->slug = Slugger::unique($product->name, self::class, $product->getKey(), 180);
            }
        });
    }

    /** @return BelongsToMany<Company, $this> */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_product')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    /** @return BelongsToMany<Project, $this> */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'product_project')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    /** @return BelongsToMany<Note, $this> */
    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_product')->withTimestamps();
    }

    /** @return MorphMany<TimelineEvent, $this> */
    public function timelineEvents(): MorphMany
    {
        return $this->morphMany(TimelineEvent::class, 'timelineable');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('launched_at');
    }

    /** آیا محصول واقعاً در دسترس کاربران است؟ */
    public function isLive(): bool
    {
        return $this->status === ProductStatus::Live;
    }

    /** آیا قابلیت‌های «برنامه‌ریزی‌شده» باید با برچسب نمایش داده شوند؟ */
    public function isPreRelease(): bool
    {
        return in_array($this->status, [ProductStatus::Idea, ProductStatus::Development], true);
    }
}
