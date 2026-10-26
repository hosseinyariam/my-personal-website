<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * پیام فرم تماس.
 *
 * طراحی: پیام پیش از تلاش برای ارسال ایمیل ذخیره می‌شود. اگر SMTP
 * قطع باشد، پیام از دست نمی‌رود — `mail_sent=false` می‌ماند و مدیر
 * در پنل می‌بیند که باید دستی پیگیری کند.
 *
 * حریم خصوصی: به‌جای IP خام، هش آن ذخیره می‌شود. برای rate limit
 * کافی است ولی داده‌ی شخصی کمتری نگه می‌دارد.
 */
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'purpose',
        'message',
        'ip_hash',
        'user_agent',
        'mail_sent',
        'is_spam',
        'is_read',
        'read_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'mail_sent' => 'boolean',
            'is_spam' => 'boolean',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false)->where('is_spam', false);
    }

    public function scopeNotSpam(Builder $query): Builder
    {
        return $query->where('is_spam', false);
    }

    public function scopeNewestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->forceFill(['is_read' => true, 'read_at' => now()])->save();
        }
    }

    /** پیام‌هایی که ارسال ایمیلشان شکست خورده — نیازمند پیگیری دستی. */
    public function scopeFailedToSend(Builder $query): Builder
    {
        return $query->where('mail_sent', false)->where('is_spam', false);
    }
}
