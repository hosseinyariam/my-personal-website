<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Support\Persian;
use Database\Factories\AchievementFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * دستاورد.
 *
 * قاعده‌ی سخت برند (Strategy:578-594 و BIM:463): هیچ دستاوردی بدون
 * شاهد منتشر نمی‌شود. `evidence_url` اختیاری است در دیتابیس، ولی
 * scope `evidenced` فقط مواردی را برای نمایش عمومی می‌پذیرد که
 * شاهد یا توضیح واقعی دارند.
 */
class Achievement extends Model
{
    /** @use HasFactory<AchievementFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'achieved_at',
        'evidence_url',
        'status',
        'is_featured',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'achieved_at' => 'date',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublishedEntries(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published->value);
    }

    /**
     * فقط دستاوردهایی که شاهد قابل ارائه دارند.
     *
     * چرا اینقدر سخت‌گیر: «دستاورد» کلمه‌ای است که بیشترین ریسک ادعای
     * بی‌پشتوانه را دارد. اگر شاهدی نیست، حداقل باید توضیح واقعی باشد.
     */
    public function scopeEvidenced(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNotNull('evidence_url')
                ->orWhere(function (Builder $inner): void {
                    $inner->whereNotNull('description')->where('description', '!=', '');
                });
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('achieved_at');
    }

    public function dateLabel(): string
    {
        return Persian::date($this->achieved_at, 'MMMM y');
    }
}
