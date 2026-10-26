<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Slugger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * دسته‌بندی یادداشت.
 *
 * تصمیم D10: هفت حوزه‌ی IA:213-220 (ساخت محصول، حل مسئله، مهندسی نرم‌افزار،
 * هوش مصنوعی، کارآفرینی، درس‌آموخته‌ها، رشد شخصی) به‌عنوان دسته‌های
 * اولیه seed می‌شوند — ولی چون جدول است، تغییرشان کد نمی‌خواهد.
 */
class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $category): void {
            if (blank($category->slug)) {
                $category->slug = Slugger::unique($category->name, self::class, $category->getKey(), 140);
            }
        });
    }

    /** @return BelongsToMany<Note, $this> */
    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_category')->withTimestamps();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * دسته‌هایی که حداقل یک یادداشت منتشرشده دارند.
     *
     * دسته‌ی خالی در ناوبری، لینکی می‌سازد که به صفحه‌ی خالی می‌رود.
     * مستندات این را صریحاً ممنوع کرده‌اند (UI Kit:88).
     */
    public function scopeWithPublishedNotes(Builder $query): Builder
    {
        return $query->whereHas('notes', function (Builder $q): void {
            $q->where('status', 'published')
                ->where('visibility', 'public')
                ->where(function (Builder $inner): void {
                    $inner->whereNull('published_at')->orWhere('published_at', '<=', now());
                });
        });
    }
}
