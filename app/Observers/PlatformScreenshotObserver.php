<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PlatformScreenshot;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PlatformScreenshotObserver
{
    public function saved(): void
    {
        Cache::forget(CacheKeys::PLATFORMS_ACTIVE);
    }

    /** فایل یتیم روی دیسک باقی نماند. */
    public function deleted(PlatformScreenshot $screenshot): void
    {
        if ($screenshot->path && Storage::disk('public')->exists($screenshot->path)) {
            Storage::disk('public')->delete($screenshot->path);
        }

        Cache::forget(CacheKeys::PLATFORMS_ACTIVE);
    }
}
