<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * رسانه — فراداده‌ی فایل، نه خود فایل.
 *
 * تصمیم معماری:275 گوید محتوای باینری هرگز در MySQL ذخیره نمی‌شود.
 * اینجا فقط مسیر و فراداده است؛ فایل روی دیسک می‌ماند.
 *
 * الزام CMS:185-188: فایل فیزیکی تا زمانی که جای دیگری استفاده نشده
 * حذف نمی‌شود. این منطق در `MediaService` است نه در رویداد حذف مدل،
 * تا حذف ناخواسته ممکن نباشد.
 */
class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory;

    protected $table = 'media';

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'alt_text',
        'caption',
        'metadata',
        'created_by',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    /** @return HasMany<MediaAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(MediaAttachment::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /** آیا این فایل تصویر است؟ کنترل‌های ابعاد فقط برای تصویر معنا دارد. */
    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    /** متن جایگزین یا یک جایگزین محترمانه — هرگز خالی نمی‌ماند. */
    public function alt(): string
    {
        return $this->alt_text ?: ($this->caption ?: $this->original_name ?: 'تصویر');
    }

    /** حجم خوانا برای نمایش در پنل. */
    public function humanSize(): string
    {
        if ($this->size_bytes === null) {
            return '—';
        }

        $units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت'];
        $size = (float) $this->size_bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        $formatted = $unit === 0
            ? \App\Support\Persian::digits((string) (int) $size)
            : \App\Support\Persian::digits(number_format($size, 1));

        return $formatted.' '.$units[$unit];
    }

    /**
     * تعداد جاهایی که این رسانه استفاده شده.
     *
     * لازم برای تصمیم حذف: اگر بیشتر از یک پیوست باشد، فایل مشترک است
     * و حذف فیزیکی آن، جای دیگر را می‌شکند.
     */
    public function usageCount(): int
    {
        return $this->attachments()->count();
    }

    public function isShared(): bool
    {
        return $this->usageCount() > 1;
    }
}
