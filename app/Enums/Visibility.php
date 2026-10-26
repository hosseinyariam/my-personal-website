<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * سطح دسترسی — جدا از وضعیت انتشار.
 *
 * `status` می‌گوید «آیا منتشر شده؟»؛ `visibility` می‌گوید «اگر منتشر شده،
 * چه کسی می‌تواند ببیند؟». یک محتوای منتشرشده می‌تواند `unlisted` باشد:
 * با لینک دیده می‌شود ولی در فهرست‌ها و sitemap نمی‌آید.
 */
enum Visibility: string
{
    case Public = 'public';
    case Unlisted = 'unlisted';
    case Private = 'private';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'عمومی',
            self::Unlisted => 'بدون فهرست',
            self::Private => 'خصوصی',
        };
    }

    /** آیا در فهرست‌ها، جست‌وجو و sitemap بیاید؟ */
    public function isListable(): bool
    {
        return $this === self::Public;
    }

    /** آیا با لینک مستقیم قابل دیدن است؟ */
    public function isViewable(): bool
    {
        return $this !== self::Private;
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c): array => [$c->value => $c->label()])
            ->all();
    }
}
