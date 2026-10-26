<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CompanyRelation;
use App\Enums\PublicationStatus;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\Publishable;
use App\Support\Slugger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * شرکت — организационная сущность.
 *
 * اصل IA:187: شرکت با محصول یا پروژه یکی نیست. یک شرکت موجودیت
 * مستقل است که می‌تواند چند محصول و چند پروژه داشته باشد.
 *
 * نکته‌ی امنیتی CMS:146-147: اطلاعات محرمانه‌ی شرکت نباید در بخش
 * عمومی بیاید. `status/visibility` در سطح رکورد کنترل می‌کند.
 */
class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;

    use HasMedia;
    use HasSeo;
    use Publishable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'summary',
        'description',
        'website_url',
        'relationship_type',
        'company_type',
        'started_at',
        'ended_at',
        'status',
        'published_at',
        'is_featured',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'relationship_type' => CompanyRelation::class,
            'status' => PublicationStatus::class,
            'started_at' => 'date',
            'ended_at' => 'date',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** جدول شرکت ستون `visibility` ندارد؛ انتشار با `status` و `published_at` است. */
    protected function hasVisibilityColumn(): bool
    {
        return false;
    }

    protected static function booted(): void
    {
        static::saving(function (self $company): void {
            if (blank($company->slug)) {
                $company->slug = Slugger::unique($company->name, self::class, $company->getKey(), 180);
            }
        });
    }

    /** @return HasMany<Experience, $this> */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    /** @return BelongsToMany<Product, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'company_product')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    /** @return BelongsToMany<Project, $this> */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'company_project')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    /** @return BelongsToMany<Note, $this> */
    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_company')->withTimestamps();
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
            ->orderByDesc('started_at');
    }

    /** آیا رابطه‌ی فعلی و باز است؟ */
    public function isOngoing(): bool
    {
        return $this->ended_at === null;
    }

    /**
     * آیا محتوای کافی برای صفحه‌ی اختصاصی دارد؟
     *
     * UI Kit:78-79 صفحه‌ی شرکت را «مشروط به محتوای کافی» کرده است.
     * شرکتی که فقط نام دارد نباید صفحه‌ی خالی بگیرد.
     */
    public function hasEnoughContent(): bool
    {
        return filled($this->summary)
            || filled($this->description)
            || $this->products()->exists()
            || $this->projects()->exists();
    }
}
