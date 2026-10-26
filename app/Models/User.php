<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * کاربر — حساب ورود به پنل مدیریت.
 *
 * این مدل عمداً هیچ داده‌ی عمومی سایت را نگه نمی‌دارد. هویت عمومی در
 * `Profile` است. نتیجه: افزودن مدیر دوم یا تغییر رمز، هرگز روی محتوای
 * سایت اثر نمی‌گذارد.
 *
 * الزام امنیتی Blueprint:331-333: رمز پیش‌فرض ثابت در کد وجود ندارد.
 * تنها راه ساخت مدیر، seed خوانده‌شده از متغیر محیطی است.
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * فیلدهای حساس هرگز سریالایز نمی‌شوند (الزام Blueprint:119).
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<Note, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'author_id');
    }

    /**
     * آیا این کاربر مجاز به ورود به پنل است؟
     *
     * فهرست مجاز از `ADMIN_EMAIL` می‌آید. خالی بودنش یعنی هیچ‌کس مجاز
     * نیست — شکست امن، نه باز. اگر این متغیر ناخواسته خالی بماند، پنل
     * قفل می‌ماند نه اینکه برای همه باز شود.
     *
     * ── چرا امضای متد `?Panel $panel = null` است ──
     * این متد هم قرارداد Filament (`FilamentUser::canAccessPanel`) را
     * پیاده می‌کند و هم بدون آرگومان از Policyها صدا زده می‌شود.
     * پیاده‌سازی `FilamentUser` حیاتی است: بدون آن، middleware پنل
     * Filament به شرط `env === 'local'` برمی‌گردد و در محیط محلی هر
     * کاربر وارد‌شده را راه می‌دهد — یعنی «وارد شده» به‌جای «مدیر».
     * با پیاده‌سازی این قرارداد، ADMIN_EMAIL تنها منبع حقیقت می‌شود
     * در همه‌ی محیط‌ها. پارامتر Panel استفاده نمی‌شود چون در MVP فقط
     * یک پنل وجود دارد و فهرست مجاز، مستقل از پنل، یکسان است.
     */
    public function canAccessPanel(?Panel $panel = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $allowed = array_filter(array_map(
            'trim',
            explode(',', (string) config('app.admin_emails', ''))
        ));

        if ($allowed === []) {
            return false;
        }

        return in_array(
            mb_strtolower((string) $this->email),
            array_map('mb_strtolower', $allowed),
            true
        );
    }
}
