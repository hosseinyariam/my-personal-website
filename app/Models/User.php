<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * دروازه‌ی ورود به پنل — سایت تک‌کاربره است و ثبت‌نام بسته (M7).
     *
     * فهرست مجاز از env می‌آید. اگر تنظیم نشده باشد، هیچ‌کس وارد نمی‌شود:
     * حالت شکستِ امن، نه دسترسی باز.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        $allowed = collect(explode(',', (string) config('app.admin_emails')))
            ->map(fn (string $email) => mb_strtolower(trim($email)))
            ->filter();

        if ($allowed->isEmpty()) {
            return false;
        }

        return $allowed->contains(mb_strtolower($this->email));
    }
}
