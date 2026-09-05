<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlatformStatus;
use App\Models\Concerns\HasActiveOrdering;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Platform extends Model
{
    use HasActiveOrdering;
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'tech_stack' => 'array',
            'status' => PlatformStatus::class,
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function screenshots(): HasMany
    {
        return $this->hasMany(PlatformScreenshot::class);
    }

    public function activeScreenshots(): HasMany
    {
        return $this->screenshots()->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** کاشی آیکون کارت — پیش‌فرضِ امن وقتی مدیر آیکونی انتخاب نکرده. */
    public function iconName(): string
    {
        return $this->icon ?: 'package';
    }

    /** فهرست امکانات، پاک‌شده از ردیف‌های ناقصِ Repeater پنل. */
    public function featureList(): array
    {
        return collect($this->features ?? [])
            ->filter(fn ($f) => is_array($f) && filled($f['title'] ?? null))
            ->values()
            ->all();
    }

    public function techStackList(): array
    {
        return collect($this->tech_stack ?? [])->filter(fn ($t) => filled($t))->values()->all();
    }

    /** آیا سکشن «مشخصات» چیزی برای نشان‌دادن دارد؟ خالی = سکشن مخفی. */
    public function hasSpecs(): bool
    {
        return filled($this->version) || filled($this->license) || $this->updated_at !== null;
    }
}
