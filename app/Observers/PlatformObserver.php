<?php

declare(strict_types=1);

namespace App\Observers;

use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

class PlatformObserver
{
    public function saved(): void
    {
        $this->flush();
    }

    public function deleted(): void
    {
        $this->flush();
    }

    public function restored(): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        Cache::forget(CacheKeys::PLATFORMS_ACTIVE);
        Cache::forget(CacheKeys::PLATFORMS_FEATURED);
    }
}
