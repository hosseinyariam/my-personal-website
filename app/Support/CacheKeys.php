<?php

declare(strict_types=1);

namespace App\Support;

/**
 * کلیدهای کش — فهرست بسته و قفل‌شده (بخش ۹.۴ سند).
 * هر کلید دقیقاً یک Observer دارد که آن را باطل می‌کند.
 */
final class CacheKeys
{
    public const PLATFORMS_ACTIVE = 'platforms.active';
    public const PLATFORMS_FEATURED = 'platforms.featured';
    public const POSTS_PUBLISHED_COUNT = 'posts.published_count';
    public const POSTS_LATEST = 'posts.latest';
    public const SOCIAL_LINKS_ACTIVE = 'social_links.active';

    /** @return array<int, string> */
    public static function all(): array
    {
        return [
            self::PLATFORMS_ACTIVE,
            self::PLATFORMS_FEATURED,
            self::POSTS_PUBLISHED_COUNT,
            self::POSTS_LATEST,
            self::SOCIAL_LINKS_ACTIVE,
        ];
    }
}
