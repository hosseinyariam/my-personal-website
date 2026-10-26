<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\PublicationStatus;
use App\Support\Persian;
use Database\Factories\ExperienceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * سابقه‌ی کاری.
 *
 * نکته‌ی مهم حذف: `company_id` با `nullOnDelete` بسته شده است، پس حذف
 * یک شرکت سابقه‌ی کاری را پاک نمی‌کند — فقط ارجاع را خالی می‌گذارد.
 * این سابقه بخشی از مسیر حرفه‌ای است، حتی اگر شرکت از سایت برود.
 */
class Experience extends Model
{
    /** @use HasFactory<ExperienceFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'employment_type',
        'location_label',
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
            'employment_type' => EmploymentType::class,
            'status' => PublicationStatus::class,
            'started_at' => 'date',
            'ended_at' => 'date',
            'is_current' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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

    /** برچسب بازه‌ی زمانی با در نظر گرفتن «تاکنون». */
    public function periodLabel(): string
    {
        return Persian::dateRange($this->started_at, $this->ended_at, $this->is_current);
    }

    /** مدت اشتغال به سال — فقط اگر تاریخ شروع معلوم باشد. */
    public function durationInYears(): ?int
    {
        if ($this->started_at === null) {
            return null;
        }

        $end = $this->is_current ? now() : ($this->ended_at ?? now());

        return max(1, (int) $this->started_at->diffInYears($end));
    }
}
