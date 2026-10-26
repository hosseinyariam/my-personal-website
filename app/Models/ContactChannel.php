<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContactChannelType;
use App\Enums\ContactPurpose;
use Database\Factories\ContactChannelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * کانال ارتباطی عمومی.
 *
 * قاعده‌ی سخت CMS:231-243: فقط کانال‌هایی که هم `is_active` و هم
 * `is_public` هستند در سایت عمومی نمایش داده می‌شوند. این کنترل در
 * scope انجام می‌شود، نه در Blade — چون «پنهان‌کردن در نما» محافظت نیست.
 *
 * اصل IA:321: هیچ ایمیل، شماره یا حساب اجتماعی حدس زده نمی‌شود.
 * این جدول تنها منبع کانال‌های عمومی است.
 */
class ContactChannel extends Model
{
    /** @use HasFactory<ContactChannelFactory> */
    use HasFactory;

    protected $fillable = [
        'label',
        'channel_type',
        'url',
        'handle',
        'purpose',
        'is_public',
        'is_active',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'channel_type' => ContactChannelType::class,
            'purpose' => ContactPurpose::class,
            'is_public' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * کانال‌های مجاز برای نمایش عمومی.
     *
     * تنها scopeی که صفحات عمومی باید استفاده کنند.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true)->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    public function scopeForPurpose(Builder $query, ContactPurpose $purpose): Builder
    {
        return $query->where('purpose', $purpose->value);
    }

    /** آیا لینک خارجی است؟ برای افزودن `rel` امنیتی لازم. */
    public function isExternal(): bool
    {
        return $this->channel_type !== ContactChannelType::Email;
    }

    /**
     * آدرس قابل استفاده در `href`.
     *
     * ── چرا این متد لازم است ──
     * مقدار `url` برای ایمیل، خودِ آدرس است (`hello@example.com`)، نه
     * یک URL. اگر مستقیم در `href` گذاشته شود، مرورگر آن را به‌عنوان
     * مسیر نسبی تفسیر می‌کند و لینک به `/hello@example.com` می‌رود —
     * لینکی که وجود ندارد. این متد پیشوند `mailto:` را اضافه می‌کند.
     *
     * برای تلفن هم همین منطق: `tel:` لازم است.
     */
    public function href(): string
    {
        $value = trim((string) $this->url);

        // اگر مدیر خودش پیشوند را گذاشته باشد، دوباره اضافه نمی‌کنیم —
        // جلوگیری از `mailto:mailto:...`
        if (preg_match('#^(mailto:|tel:|https?://)#i', $value) === 1) {
            return $value;
        }

        return match ($this->channel_type) {
            ContactChannelType::Email => 'mailto:'.$value,
            ContactChannelType::Phone => 'tel:'.preg_replace('/[^0-9+]/', '', $value),
            // وب‌سایت و شبکه‌ی اجتماعی بدون پیشوند، URL ناقص‌اند
            ContactChannelType::Website, ContactChannelType::Social => str_starts_with($value, '//')
                ? 'https:'.$value
                : 'https://'.ltrim($value, '/'),
            default => $value,
        };
    }
}
