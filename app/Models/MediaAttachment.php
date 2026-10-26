<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * پیوست رسانه — جدول واسط چندریختی.
 *
 * نکته‌ی امنیتی معماری:289: نوع موجودیت متصل‌شونده فقط از registry
 * داخلی پذیرفته می‌شود. اینجا یک `assertAllowedType` هست تا اگر
 * روزی کسی تلاش کرد نوع دلخواه از سمت کلاینت بفرستد، استثنا بخورد.
 */
class MediaAttachment extends Model
{
    /**
     * انواع مجاز پیوست — allowlist صریح، نه blocklist.
     *
     * @var list<class-string<Model>>
     */
    public const ALLOWED_TYPES = [
        Profile::class,
        Project::class,
        Product::class,
        Company::class,
        Note::class,
        TimelineEvent::class,
    ];

    protected $fillable = [
        'media_id',
        'mediable_type',
        'mediable_id',
        'role',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<Media, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /** @return MorphTo<Model, $this> */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    protected static function booted(): void
    {
        static::saving(function (self $attachment): void {
            if (! in_array($attachment->mediable_type, self::ALLOWED_TYPES, true)) {
                throw new \InvalidArgumentException(
                    "نوع موجودیت «{$attachment->mediable_type}» برای پیوست رسانه مجاز نیست."
                );
            }
        });
    }
}
