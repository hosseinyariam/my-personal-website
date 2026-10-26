<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SettingType;
use App\Support\CacheKeys;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * تنظیمات عمومی سایت.
 *
 * قاعده‌ی امنیتی سخت (Blueprint:545-548): این جدول **هرگز راز نگه
 * نمی‌دارد**. کلید API، رمز و توکن فقط در `ai_providers` با cast
 * رمزنگاری‌شده. اگر روزی کسی تلاش کرد کلید اینجا بگذارد،
 * `SettingService` جلویش را می‌گیرد.
 *
 * `is_public` تعیین می‌کند مقدار در نماهای عمومی سریالایز شود یا نه.
 * پیش‌فرض `false` است — شکست امن.
 */
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'is_public',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => SettingType::class,
            'is_public' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(CacheKeys::PUBLIC_SETTINGS));
        static::deleted(fn () => Cache::forget(CacheKeys::PUBLIC_SETTINGS));
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeInGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * مقدار با نوع درست برگردانده می‌شود.
     *
     * چرا در مدل تبدیل می‌کنیم: بدون آن، `is_public` یک رشته‌ی
     * `"0"`/`"1"` برمی‌گرداند و در شرط‌های PHP غافلگیری می‌سازد.
     * ستون `value` متنی است تا انواع مختلف را نگه دارد.
     */
    public function typedValue(): mixed
    {
        $raw = $this->value;

        if ($raw === null) {
            return null;
        }

        return match ($this->type) {
            SettingType::Boolean => filter_var($raw, FILTER_VALIDATE_BOOL),
            SettingType::Integer => (int) $raw,
            SettingType::Json => json_decode((string) $raw, true),
            SettingType::String => (string) $raw,
        };
    }
}
