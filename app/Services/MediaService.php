<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use App\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * سرویس رسانه — آپلود، اتصال و حذف امن.
 *
 * ── الزامات امنیتی که اینجا اعمال می‌شوند (CMS:185-188) ──
 *   ۱. اعتبارسنجی واقعی MIME، نه فقط پسوند فایل
 *   ۲. نام ذخیره‌سازی تصادفی — جلوگیری از مسیر دلخواه و بازنویسی فایل
 *   ۳. سقف حجم
 *   ۴. جلوگیری از اجرای فایل آپلودشده
 *   ۵. حذف فیزیکی فقط پس از بررسی اینکه جای دیگری استفاده نمی‌شود
 *
 * نکته‌ی مهم درباره‌ی حذف: این سرویس هرگز با حذف یک پیوست، فایل را
 * پاک نمی‌کند. دلیل: یک تصویر می‌تواند در چند محتوا استفاده شود
 * (مثلاً لوگوی شرکت در سه پروژه). حذف ناخواسته، جای دیگر را می‌شکند.
 * ────────────────────────────────────────────────────────────
 */
class MediaService
{
    /** سقف حجم به کیلوبایت — ۵ مگابایت کافی است و هاست اشتراکی را پر نمی‌کند. */
    public const MAX_SIZE_KB = 5120;

    /**
     * انواع مجاز — allowlist صریح.
     *
     * چرا allowlist و نه blocklist: blocklist همیشه یک فرمت فراموش‌شده
     * دارد (svg، swf، phtml…). allowlist امن است چون پیش‌فرضش رد است.
     *
     * توجه: SVG عمداً مجاز **نیست**. SVG می‌تواند جاوااسکریپت اجرا کند
     * و در همان مبدأ ذخیره می‌شود، پس یک بردار XSS ذخیره‌شده است.
     *
     * @var array<string, list<string>>
     */
    public const ALLOWED_MIME = [
        'image' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'],
        'document' => ['application/pdf'],
    ];

    /**
     * آپلود یک فایل و ساخت رکورد رسانه.
     *
     * @throws \InvalidArgumentException اگر نوع یا حجم مجاز نباشد
     */
    public function store(UploadedFile $file, ?int $userId = null, ?string $altText = null): Media
    {
        $this->assertFileIsAllowed($file);

        return DB::transaction(function () use ($file, $userId, $altText): Media {
            $mime = (string) $file->getMimeType();
            $extension = $this->safeExtension($file, $mime);

            // نام تصادفی: جلوگیری از حدس مسیر، بازنویسی فایل موجود،
            // و اجرای فایل با نام آپلودشده توسط کاربر.
            $filename = bin2hex(random_bytes(16)).'.'.$extension;

            // مسیر بر اساس تاریخ، تا یک پوشه با هزاران فایل ساخته نشود —
            // برخی فایل‌سیستم‌ها و پنل‌ها در این حالت کند می‌شوند.
            $directory = 'media/'.now()->format('Y/m');
            $path = $file->storeAs($directory, $filename, 'public');

            $dimensions = $this->imageDimensions($file, $mime);

            return Media::create([
                'disk' => 'public',
                'path' => $path,
                'original_name' => $this->sanitizeOriginalName($file->getClientOriginalName()),
                'mime_type' => $mime,
                'size_bytes' => $file->getSize(),
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
                'alt_text' => $altText,
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * اتصال یک رسانه به یک موجودیت.
     */
    public function attach(Media $media, Model $model, string $role = 'default', int $sortOrder = 0): MediaAttachment
    {
        return MediaAttachment::create([
            'media_id' => $media->getKey(),
            'mediable_type' => $model->getMorphClass(),
            'mediable_id' => $model->getKey(),
            'role' => $role,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * جداکردن رسانه از یک موجودیت — بدون حذف فایل.
     */
    public function detach(Media $media, Model $model): void
    {
        MediaAttachment::query()
            ->where('media_id', $media->getKey())
            ->where('mediable_type', $model->getMorphClass())
            ->where('mediable_id', $model->getKey())
            ->delete();
    }

    /**
     * حذف کامل یک رسانه — فایل و رکورد.
     *
     * ── چرا این متد باید با احتیاط صدا زده شود ──
     * اگر رسانه در بیش از یک جا استفاده شده باشد، حذف فایل آن،
     * محتوای دیگر را می‌شکند. پس این متد فقط پس از تأیید صریح مدیر
     * و با نمایش تعداد استفاده صدا زده می‌شود.
     *
     * @param  bool  $force  حذف حتی اگر جای دیگری استفاده شده باشد
     */
    public function delete(Media $media, bool $force = false): bool
    {
        if (! $force && $media->isShared()) {
            throw new \RuntimeException(
                "این رسانه در {$media->usageCount()} جا استفاده شده است. "
                .'حذف آن محتوای دیگر را می‌شکند. برای حذف اجباری، force را true کنید.'
            );
        }

        return DB::transaction(function () use ($media): bool {
            $disk = $media->disk;
            $path = $media->path;

            // ابتدا رکوردها، بعد فایل — اگر حذف فایل شکست بخورد،
            // رکورد یتیم نمی‌ماند که به فایل ناموجود اشاره کند.
            $media->attachments()->delete();
            $deleted = $media->delete();

            if ($deleted && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }

            return $deleted;
        });
    }

    /**
     * اعتبارسنجی سخت‌گیرانه‌ی فایل.
     *
     * @throws \InvalidArgumentException
     */
    private function assertFileIsAllowed(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw new \InvalidArgumentException('آپلود فایل شکست خورد یا فایل ناقص است.');
        }

        if ($file->getSize() > self::MAX_SIZE_KB * 1024) {
            $max = \App\Support\Persian::digits((string) (self::MAX_SIZE_KB / 1024));

            throw new \InvalidArgumentException("حجم فایل بیشتر از {$max} مگابایت است.");
        }

        // MIME واقعی از محتوای فایل خوانده می‌شود، نه از پسوند یا
        // هدر ارسالی کلاینت — این دو قابل جعل هستند.
        $mime = (string) $file->getMimeType();
        $allowed = array_merge(...array_values(self::ALLOWED_MIME));

        if (! in_array($mime, $allowed, true)) {
            throw new \InvalidArgumentException(
                "نوع فایل «{$mime}» مجاز نیست. انواع مجاز: تصویر (JPEG، PNG، WebP، GIF، AVIF) و PDF."
            );
        }

        // لایه‌ی دفاعی دوم: فایل تصویری باید واقعاً با تابع تصویر باز شود.
        // فایلی با MIME جعلی اینجا رد می‌شود.
        if (str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml') {
            $path = $file->getRealPath();

            if ($path !== false && @getimagesize($path) === false) {
                throw new \InvalidArgumentException('فایل تصویری معتبر نیست.');
            }
        }
    }

    private function safeExtension(UploadedFile $file, string $mime): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/avif' => 'avif',
            'application/pdf' => 'pdf',
        ];

        // پسوند از MIME تأییدشده گرفته می‌شود، نه از نام فایل کاربر.
        // اینطور آپلود `evil.php` با MIME تصویر، پسوند تصویر می‌گیرد.
        return $map[$mime] ?? 'bin';
    }

    private function sanitizeOriginalName(string $name): string
    {
        $name = basename($name);
        $name = preg_replace('/[^\p{L}\p{N}.\-_ ]+/u', '', $name) ?? $name;

        return mb_substr(trim($name), 0, 255) ?: 'file';
    }

    /**
     * @return array{width: int|null, height: int|null}
     */
    private function imageDimensions(UploadedFile $file, string $mime): array
    {
        if (! str_starts_with($mime, 'image/')) {
            return ['width' => null, 'height' => null];
        }

        $path = $file->getRealPath();

        if ($path === false) {
            return ['width' => null, 'height' => null];
        }

        $size = @getimagesize($path);

        return [
            'width' => $size[0] ?? null,
            'height' => $size[1] ?? null,
        ];
    }
}
