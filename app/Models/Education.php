<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Support\Persian;
use Database\Factories\EducationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * تحصیلات.
 *
 * توجه به نام جدول: `education` مفرد است — همان‌طور که ERD:143 خواسته.
 * Laravel به‌طور پیش‌فرض جدول را `educations` می‌گرفت، پس صریحاً تنظیم شد.
 */
class Education extends Model
{
    /** @use HasFactory<EducationFactory> */
    use HasFactory;

    protected $table = 'education';

    protected $fillable = [
        'institution',
        'degree',
        'field_of_study',
        'description',
        'started_at',
        'ended_at',
        'is_current',
        'status',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'started_at' => 'date',
            'ended_at' => 'date',
            'is_current' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublishedEntries(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published->value);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_current')
            ->orderBy('sort_order')
            ->orderByDesc('started_at');
    }

    public function periodLabel(): string
    {
        return Persian::dateRange($this->started_at, $this->ended_at, $this->is_current);
    }

    /** عنوان کامل: مدرک + رشته، با حذف بخش‌های خالی. */
    public function fullTitle(): string
    {
        return collect([$this->degree, $this->field_of_study])
            ->filter()
            ->implode(' · ') ?: $this->institution;
    }
}
