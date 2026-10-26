<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Slugger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * فناوری.
 *
 * فناوری توضیح‌دهنده است، نه فخر‌فروش. اصل Strategy:505 «Evidence > Skill List»
 * و هشدار درباره‌ی «فهرست طولانی فناوری بدون شاهد» — پس فناوری‌ها
 * در کنار پروژه‌ای که با آن‌ها ساخته شده معنا پیدا می‌کنند.
 */
class Technology extends Model
{
    /** @use HasFactory<\Database\Factories\TechnologyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'website_url',
        'icon_key',
        'sort_order',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $technology): void {
            if (blank($technology->slug)) {
                $technology->slug = Slugger::unique($technology->name, self::class, $technology->getKey(), 140);
            }
        });
    }

    /** @return BelongsToMany<Project, $this> */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_technology')->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * فناوری‌هایی که در یک پروژه‌ی عمومی استفاده شده‌اند.
     *
     * توجه (تصمیم D17): معیار انتشار پروژه `published_at` و
     * `visibility` است، نه `status` — چون `status` چرخه‌ی دامنه است.
     */
    public function scopeEvidenced(Builder $query): Builder
    {
        return $query->whereHas('projects', function (Builder $q): void {
            $q->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where('visibility', 'public');
        });
    }
}
