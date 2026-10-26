<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NoteType;
use App\Enums\PublicationStatus;
use App\Enums\Visibility;
use App\Models\Concerns\HasMedia;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\Publishable;
use App\Support\Persian;
use App\Support\Slugger;
use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * یادداشت / مقاله.
 *
 * الگوی محتوایی برند (Strategy:929-941): هر یادداشت باید حداقل بخشی از
 * چرخه‌ی «مسئله → فکر → ساخت → نتیجه → یادگیری» را نشان دهد. این در
 * اعتبارسنجی الزامی نشده (سخت‌گیری بیش از حد مانع نوشتن می‌شود) اما
 * در راهنمای محتوای پنل به مدیر یادآوری می‌شود.
 *
 * زمان مطالعه ذخیره نمی‌شود و محاسبه می‌شود — CMS:150-160.
 */
class Note extends Model
{
    /** @use HasFactory<NoteFactory> */
    use HasFactory;

    use HasMedia;
    use HasSeo;
    use Publishable;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'type',
        'status',
        'visibility',
        'published_at',
        'author_id',
        'is_featured',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => NoteType::class,
            'status' => PublicationStatus::class,
            'visibility' => Visibility::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $note): void {
            if (blank($note->slug)) {
                $note->slug = Slugger::unique($note->title, self::class, $note->getKey(), 220);
            }

            // خلاصه اگر خالی بماند از متن ساخته می‌شود. صفحه‌ی فهرست
            // بدون خلاصه، کارت‌های بی‌معنا تولید می‌کند.
            if (blank($note->excerpt) && filled($note->body)) {
                $note->excerpt = Persian::excerpt($note->body, 180);
            }
        });
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'note_category')->withTimestamps();
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'note_tag')->withTimestamps();
    }

    /** @return BelongsToMany<Project, $this> */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'note_project')->withTimestamps();
    }

    /** @return BelongsToMany<Product, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'note_product')->withTimestamps();
    }

    /** @return BelongsToMany<Company, $this> */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'note_company')->withTimestamps();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at');
    }

    public function scopeOfType(Builder $query, NoteType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    /** زمان مطالعه به دقیقه — محاسبه‌شده، نه ذخیره‌شده. */
    public function readingTimeMinutes(): int
    {
        return Persian::readingTimeMinutes($this->body);
    }

    public function readingTimeLabel(): string
    {
        return Persian::readingTime($this->body);
    }

    /**
     * تاریخ مؤثر برای نمایش: اگر تاریخ انتشار تنظیم شده از آن، وگرنه
     * تاریخ ویرایش. IA:224 می‌گوید تاریخ را فقط وقتی نشان بده که
     * معنای مشخص دارد — پس هیچ‌وقت تاریخ جعلی تولید نمی‌کنیم.
     */
    public function displayDate(): ?\DateTimeInterface
    {
        return $this->published_at ?? $this->updated_at;
    }
}
