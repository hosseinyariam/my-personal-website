<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\Visibility;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\Publishable;
use App\Support\Slugger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * پروژه — سابقه‌ی ساخت یک چیز.
 *
 * تفاوت بنیادی با Product (اصل IA:602): پروژه «کاری که انجام شد» است؛
 * محصول «چیزی که به بازار عرضه شد». یک پروژه می‌تواند دانشگاهی، فریلنس،
 * یا یک تجربه‌ی ناموفق باشد. حتی پروژه‌ی متوقف‌شده ارزش نمایش دارد،
 * چون «Lessons Learned» بخشی از هویت سازنده است.
 *
 * ── انتشار (تصمیم D17) ──
 * ستون `status` اینجا **چرخه‌ی دامنه** است (ایده، در حال ساخت، تکمیل)،
 * نه وضعیت انتشار. یک پروژه‌ی «تکمیل‌شده» لزوماً نباید روی سایت باشد.
 * پس انتشار فقط با `published_at` سنجیده می‌شود: پروژه تا وقتی مدیر
 * تاریخ انتشار ندهد، عمومی نیست. `visibility` هم به‌عنوان لایه‌ی دوم
 * کنترل دسترسی وجود دارد.
 */
class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    use HasMedia;
    use HasSeo;
    use Publishable;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'problem',
        'context',
        'role',
        'contribution',
        'outcome',
        'lessons_learned',
        'status',
        'visibility',
        'started_at',
        'completed_at',
        'repository_url',
        'live_url',
        'published_at',
        'is_featured',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'visibility' => Visibility::class,
            'started_at' => 'date',
            'completed_at' => 'date',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * `status` چرخه‌ی دامنه است، پس معیار انتشار نیست.
     * انتشار اینجا فقط با `published_at` تعیین می‌شود.
     */
    protected function publicationStatusColumn(): ?string
    {
        return null;
    }

    protected static function booted(): void
    {
        // Slug اگر خالی بماند از عنوان ساخته می‌شود و یکتایی‌اش تضمین می‌شود.
        // این کار در `saving` انجام می‌شود نه در Controller، تا import،
        // seed و پنل همگی همان رفتار را بگیرند.
        static::saving(function (self $project): void {
            if (blank($project->slug)) {
                $project->slug = Slugger::unique($project->title, self::class, $project->getKey(), 180);
            }
        });
    }

    /** @return BelongsToMany<Product, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_project')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    /** @return BelongsToMany<Company, $this> */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_project')
            ->withPivot('relationship_type')
            ->withTimestamps();
    }

    /** @return BelongsToMany<Skill, $this> */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'project_skill')->withTimestamps();
    }

    /** @return BelongsToMany<Technology, $this> */
    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class, 'project_technology')->withTimestamps();
    }

    /** @return BelongsToMany<Note, $this> */
    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_project')->withTimestamps();
    }

    /** @return MorphMany<TimelineEvent, $this> */
    public function timelineEvents(): MorphMany
    {
        return $this->morphMany(TimelineEvent::class, 'timelineable');
    }

    /** ترتیب نمایش در فهرست عمومی: ویژگی‌دارها اول، بعد تازه‌ترین. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('started_at');
    }

    /** آیا این پروژه در چرخه‌ی ساخت فعال است؟ برای بخش «تمرکز فعلی». */
    public function isActive(): bool
    {
        return $this->status === ProjectStatus::InProgress;
    }

    /**
     * درصد کامل‌بودن پرونده‌ی پروژه.
     *
     * در پنل استفاده می‌شود تا مدیر ببیند کدام صفحه‌ی پروژه هنوز ناقص
     * است. نمایش عمومی به این مقدار کاری ندارد.
     */
    public function completeness(): int
    {
        $checks = [
            filled($this->summary),
            filled($this->problem),
            filled($this->context),
            filled($this->role),
            filled($this->outcome),
            filled($this->lessons_learned),
            $this->technologies()->exists(),
        ];

        $filled = count(array_filter($checks));

        return (int) round(($filled / count($checks)) * 100);
    }
}
