<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\CacheKeys;
use Database\Factories\HomepageSectionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * بخش صفحه اصلی — ترتیب و فعال‌بودن بخش‌ها.
 *
 * این جدول فقط **ترتیب و پیکربندی** نگه می‌دارد؛ محتوا در جدول خودش
 * می‌ماند (Blueprint:521-533). نتیجه: خاموش‌کردن یک بخش، هیچ داده‌ای
 * را حذف نمی‌کند و روشن‌کردن دوباره‌اش آن را برمی‌گرداند.
 *
 * اصل صریح مستندات: این یک «صفحه‌ساز نامحدود» نیست. فقط از میان
 * بخش‌های از پیش طراحی‌شده می‌توان انتخاب و مرتب کرد (UI Kit:109).
 */
class HomepageSection extends Model
{
    /** @use HasFactory<HomepageSectionFactory> */
    use HasFactory;

    /**
     * بخش‌های مجاز صفحه اصلی — allowlist صریح.
     *
     * هر `key` اینجا باید در Composer صفحه اصلی پشتیبانی شده باشد.
     * اگر مدیری بخواهد بخش ناشناخته بسازد، هیچ چیز رندر نمی‌شود؛
     * به همین دلیل allowlist لازم است.
     *
     * @var array<string, string>
     */
    public const ALLOWED_KEYS = [
        'hero' => 'معرفی کوتاه',
        'current_focus' => 'تمرکز فعلی',
        'featured_projects' => 'پروژه‌های منتخب',
        'featured_products' => 'محصولات منتخب',
        'how_i_think' => 'رویکرد من',
        'selected_notes' => 'یادداشت‌های منتخب',
        'journey_preview' => 'مسیر حرفه‌ای',
        'biography_preview' => 'درباره من',
        'contact_cta' => 'دعوت به ارتباط',
    ];

    protected $fillable = [
        'key',
        'title',
        'module_key',
        'is_enabled',
        'sort_order',
        'selection_mode',
        'display_limit',
        'settings',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
            'display_limit' => 'integer',
            'settings' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(CacheKeys::HOMEPAGE));
        static::deleted(fn () => Cache::forget(CacheKeys::HOMEPAGE));
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    /**
     * بخش‌های قابل نمایش، با دقت به وضعیت ماژول بالادستی.
     *
     * اگر ماژول مرتبط خاموش باشد، بخش هم نباید نمایش داده شود —
     * وگرنه صفحه‌ی اصلی بخشی را نشان می‌دهد که صفحه‌ی مقصدش ۴۰۴ است.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->enabled()
            ->orderBy('sort_order')
            ->get()
            ->filter(function (self $section): bool {
                if (blank($section->module_key)) {
                    return true;
                }

                return Module::isEnabled((string) $section->module_key);
            })
            ->values()
            ->toBase();
    }

    public function label(): string
    {
        return $this->title ?: (self::ALLOWED_KEYS[$this->key] ?? $this->key);
    }

    /** محدودیت تعداد آیتم؛ اگر ست نشده باشد پیش‌فرض امن. */
    public function limit(int $default = 3): int
    {
        $limit = $this->display_limit ?? $default;

        return max(1, min($limit, 12));
    }
}
