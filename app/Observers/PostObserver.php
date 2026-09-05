<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Post;
use App\Services\ReadingTime;
use App\Support\CacheKeys;
use App\Support\Slugger;
use Illuminate\Support\Facades\Cache;

class PostObserver
{
    /** زمان مطالعه همیشه از محتوا محاسبه می‌شود — هرگز دستی وارد نمی‌شود. */
    public function saving(Post $post): void
    {
        $post->reading_time = ReadingTime::forMarkdown($post->content);

        if (blank($post->slug)) {
            $post->slug = Slugger::unique($post->title, Post::class, $post->getKey());
        }
    }

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
        Cache::forget(CacheKeys::POSTS_PUBLISHED_COUNT);
        Cache::forget(CacheKeys::POSTS_LATEST);
    }
}
