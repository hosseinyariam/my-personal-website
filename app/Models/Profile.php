<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * پروفایل — هویت عمومی صاحب سایت.
 *
 * فقط یک پروفایل می‌تواند فعال باشد. این قید در دیتابیس اعمال نمی‌شود
 * (چون MySQL راه تمیزی برایش ندارد) بلکه در لایه‌ی اپلیکیشن و با تست
 * تضمین می‌شود — دقیقاً همان‌طور که ERD:54-64 و Blueprint:161-164 خواسته‌اند.
 */
class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'display_name',
        'professional_title',
        'short_bio',
        'biography',
        'positioning_statement',
        'philosophy',
        'core_values',
        'current_mission',
        'availability_status',
        'location_label',
        'email_public',
        'avatar_media_id',
        'resume_media_id',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'core_values' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Media, $this> */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'avatar_media_id');
    }

    /** @return BelongsTo<Media, $this> */
    public function resume(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'resume_media_id');
    }

    /** پروفایل فعال — تنها منبع هویت برای صفحات عمومی. */
    public static function active(): ?self
    {
        return static::query()->where('is_active', true)->first();
    }

    /**
     * فعال‌کردن این پروفایل و غیرفعال‌کردن بقیه، در یک تراکنش.
     *
     * بدون تراکنش ممکن است دو پروفایل فعال بمانند و صفحه‌ی اصلی
     * به‌طور تصادفی یکی را نشان دهد.
     */
    public function activateExclusively(): void
    {
        static::query()
            ->withoutGlobalScopes()
            ->whereKeyNot($this->getKey())
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $this->forceFill(['is_active' => true])->save();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** آیا رزومه‌ی قابل دانلود دارد؟ برای نمایش مشروط دکمه. */
    public function hasResume(): bool
    {
        return $this->resume_media_id !== null && $this->resume !== null;
    }
}
