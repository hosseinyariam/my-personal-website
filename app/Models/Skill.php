<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Slugger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * مهارت.
 *
 * نکته‌ی مهم درباره‌ی «سطح»: UI Kit:222-227 و اصل «Evidence Over Claims»
 * می‌گویند سطح مهارت نباید به‌عنوان عدد یا نوار درصدی نمایش داده شود —
 * چون امتیاز خودارزیابی، شاهد نیست. به همین دلیل `proficiency_label`
 * یک متن اختیاری است («آشنا»، «حرفه‌ای») و در سایت عمومی با تردید و
 * در صورت وجود شواهد نمایش داده می‌شود.
 */
class Skill extends Model
{
    /** @use HasFactory<\Database\Factories\SkillFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'proficiency_label',
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
        static::saving(function (self $skill): void {
            if (blank($skill->slug)) {
                $skill->slug = Slugger::unique($skill->name, self::class, $skill->getKey(), 140);
            }
        });
    }

    /** @return BelongsToMany<Project, $this> */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_skill')->withTimestamps();
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
     * مهارت‌هایی که شاهد واقعی دارند.
     *
     * در سایت عمومی فقط همین‌ها باید نمایش داده شوند: مهارتی که به هیچ
     * پروژه‌ای وصل نیست، ادعای بی‌شاهد است.
     */
    public function scopeEvidenced(Builder $query): Builder
    {
        return $query->whereHas('projects', function (Builder $q): void {
            $q->where('status', 'published')->where('visibility', 'public');
        });
    }
}
