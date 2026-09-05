<?php

declare(strict_types=1);

namespace App\Observers;

use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

class SocialLinkObserver
{
    public function saved(): void
    {
        Cache::forget(CacheKeys::SOCIAL_LINKS_ACTIVE);
    }

    public function deleted(): void
    {
        Cache::forget(CacheKeys::SOCIAL_LINKS_ACTIVE);
    }
}
