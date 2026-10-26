<?php

declare(strict_types=1);

namespace App\Support;

/**
 * کلیدهای کش در یک محل.
 *
 * چرا متمرکز: انتشار محتوا باید کش درست را باطل کند. اگر کلیدها
 * به‌صورت رشته‌ی پراکنده در Controllerها باشند، invalidation دیر یا
 * زود یکی را جا می‌گذارد و کاربر محتوای کهنه می‌بیند. معماری:1006
 * باطل‌کردن کش پس از انتشار را الزام کرده است.
 */
final class CacheKeys
{
    public const HOMEPAGE = 'homepage.composition';

    public const NAVIGATION = 'public.navigation';

    public const PUBLIC_SETTINGS = 'public.settings';

    public const SITEMAP = 'public.sitemap';

    public const MODULE_PREFIX = 'module.enabled.';

    /** کش فهرست یک ماژول (مثلاً `list.projects.1`). */
    public static function list(string $module, int $page = 1): string
    {
        return "list.{$module}.{$page}";
    }

    /** کش یک رکورد تکی. */
    public static function item(string $module, string $slug): string
    {
        return "item.{$module}.{$slug}";
    }

    public static function moduleEnabled(string $key): string
    {
        return self::MODULE_PREFIX.$key;
    }

    /**
     * کلیدهایی که با هر انتشار باطل می‌شوند.
     *
     * فهرست صریح است نه الگوی wildcard، چون `Cache::forget` الگو
     * نمی‌پذیرد و پاک‌کردن همه‌ی کش، صفحه‌های پرترافیک را بی‌دلیل سرد می‌کند.
     *
     * @return array<int, string>
     */
    public static function invalidatedOnPublish(): array
    {
        return [
            self::HOMEPAGE,
            self::NAVIGATION,
            self::SITEMAP,
        ];
    }
}
