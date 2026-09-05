<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * بهینه‌سازی تصویر در لحظه‌ی آپلود (فاز ۳ · ریسک منابع هاست اشتراکی).
 *
 * روی هاست اشتراکی worker نداریم، پس کار همین‌جا و به‌صورت هم‌زمان انجام
 * می‌شود: تغییر اندازه تا حداکثر ۱۶۰۰ پیکسل و تبدیل به WebP.
 * اگر پردازش به هر دلیلی شکست بخورد، فایل اصلی ذخیره می‌شود تا آپلود از دست نرود.
 */
final class ImageOptimizer
{
    public const MAX_WIDTH = 1600;

    public const QUALITY = 82;

    /** @return string مسیر نسبی فایل ذخیره‌شده روی دیسک public */
    public function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $name = Str::random(24);
        $target = trim($directory, '/')."/{$name}.webp";

        try {
            $image = (new ImageManager(new Driver))->read($file->getRealPath());

            if ($image->width() > self::MAX_WIDTH) {
                $image->scaleDown(width: self::MAX_WIDTH);
            }

            Storage::disk($disk)->put($target, (string) $image->toWebp(self::QUALITY));

            return $target;
        } catch (\Throwable $e) {
            report($e);

            // بازگشت امن: فایل اصلی، بدون بهینه‌سازی.
            $fallback = trim($directory, '/')."/{$name}.".$file->getClientOriginalExtension();
            Storage::disk($disk)->putFileAs(dirname($fallback), $file, basename($fallback));

            return $fallback;
        }
    }
}
