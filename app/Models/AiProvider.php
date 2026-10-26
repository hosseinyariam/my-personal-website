<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AiProviderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * فراهم‌کننده‌ی هوش مصنوعی.
 *
 * وضعیت در MVP: جدول وجود دارد، هیچ رکوردی ندارد و `AI_ENABLED=false`
 * است. ساختار آماده است تا وقتی کاربرد واقعی تعریف شد، فقط تنظیمات
 * لازم باشد نه معماری (Blueprint:521 و :775-777).
 *
 * قاعده‌ی امنیتی کلیدی: `api_key` با cast `encrypted` ذخیره می‌شود.
 * هرگز در پاسخ HTTP، HTML، JS، لاگ یا پیام خطا ظاهر نمی‌شود
 * (معماری:1043 و Blueprint:458-459). متد `toArray` عمداً آن را
 * حذف می‌کند تا حتی سریالایز اتفاقی هم نشود.
 */
class AiProvider extends Model
{
    /** @use HasFactory<AiProviderFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'provider_key',
        'base_url',
        'model_name',
        'api_key',
        'is_enabled',
        'is_default',
        'options',
    ];

    /**
     * کلید هرگز سریالایز نمی‌شود — نه در JSON، نه در پاسخ پنل.
     *
     * @var list<string>
     */
    protected $hidden = [
        'api_key',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'options' => 'array',
        ];
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * فراهم‌کننده‌ی پیش‌فرض فعلی.
     *
     * قاعده‌ی Blueprint:567-569: فقط یک رکورد می‌تواند `is_default`
     * باشد. تضمین در سرویس با تراکنش انجام می‌شود؛ اینجا فقط خواندن.
     */
    public static function default(): ?self
    {
        return static::query()
            ->where('is_default', true)
            ->where('is_enabled', true)
            ->first();
    }

    /** آیا کلید تنظیم شده؟ بدون افشای مقدار. برای نمایش وضعیت در پنل. */
    public function hasKey(): bool
    {
        return filled($this->getRawOriginal('api_key'));
    }

    /**
     * تبدیل به آرایه‌ای که برای نمایش در پنل امن است.
     *
     * @return array<string, mixed>
     */
    public function toSafeArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'provider_key' => $this->provider_key,
            'base_url' => $this->base_url,
            'model_name' => $this->model_name,
            'has_key' => $this->hasKey(),
            'is_enabled' => $this->is_enabled,
            'is_default' => $this->is_default,
        ];
    }
}
