<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ModuleKey;
use App\Support\CacheKeys;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * ماژول — کنترل روشن/خاموش یک بخش سایت.
 *
 * اصل معماری:488: «هیچ نمای Blade نباید مستقیماً تصمیم بگیرد چه چیزی
 * نمایش داده شود». این جدول منبع حقیقت است؛ middleware `module:key`
 * و کوئری‌های عمومی از آن می‌خوانند.
 *
 * اصل کلیدی: خاموش‌کردن ماژول داده را حذف نمی‌کند و دسترسی پنل را
 * تغییر نمی‌دهد. «خاموش» یعنی «مسیر عمومی غیرفعال»، نه «غیرمجاز».
 */
class Module extends Model
{
    /** @use HasFactory<\Database\Factories\ModuleFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'is_enabled',
        'settings',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'settings' => 'array',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // با تغییر وضعیت یا ترتیب، کش ماژول باید فوراً باطل شود.
        // بدون این، کاربر تا انقضای TTL محتوای یک ماژول خاموش را می‌بیند.
        static::saved(function (self $module): void {
            Cache::forget(CacheKeys::moduleEnabled((string) $module->key));
            Cache::forget(CacheKeys::NAVIGATION);
            Cache::forget(CacheKeys::HOMEPAGE);
        });

        static::deleted(function (self $module): void {
            Cache::forget(CacheKeys::moduleEnabled((string) $module->key));
            Cache::forget(CacheKeys::NAVIGATION);
            Cache::forget(CacheKeys::HOMEPAGE);
        });
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('key');
    }

    /**
     * آیا ماژولی با این کلید روشن است؟
     *
     * نتیجه کش می‌شود چون middleware روی هر درخواست عمومی این را
     * می‌پرسد و Query تکراری در هر درخواست هدر است. کش در خود مدل
     * باطل می‌شود (booted).
     *
     * رفتار در حالت نبود رکورد: **روشن** فرض می‌شود. دلیل: اگر
     * seed اجرا نشده باشد، سایت نباید کل صفحاتش را ۴۰۴ کند. برای
     * خاموش‌کردن صریح، مدیر باید رکورد را خاموش کند.
     */
    public static function isEnabled(ModuleKey|string $key): bool
    {
        $keyValue = $key instanceof ModuleKey ? $key->value : $key;

        return (bool) Cache::remember(
            CacheKeys::moduleEnabled($keyValue),
            now()->addMinutes(30),
            fn (): bool => (bool) static::query()
                ->where('key', $keyValue)
                ->value('is_enabled') ?? true
        );
    }

    public function keyEnum(): ?ModuleKey
    {
        return ModuleKey::tryFrom((string) $this->key);
    }
}
