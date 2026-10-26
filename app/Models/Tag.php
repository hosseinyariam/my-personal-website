<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Slugger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * برچسب.
 *
 * برچسب با دسته فرق دارد: دسته سلسله‌مراتب موضوعی است، برچسب
 * ابزار اتصال عرضی. یک یادداشت معمولاً یک دسته ولی چند برچسب دارد.
 */
class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $tag): void {
            if (blank($tag->slug)) {
                $tag->slug = Slugger::unique($tag->name, self::class, $tag->getKey(), 120);
            }
        });
    }

    /** @return BelongsToMany<Note, $this> */
    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_tag')->withTimestamps();
    }

    /** برچسب‌هایی که پشتشان یادداشت منتشرشده هست. */
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
