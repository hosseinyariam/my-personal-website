<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Support\Persian;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * گواهی‌نامه.
 *
 * توجه به `expires_at`: یک گواهی می‌تواند منقضی شده باشد. نمایش گواهی
 * منقضی‌شده به‌عنوان «معتبر» یک ادعای نادرست است، پس متد `isExpired`
 * وجود دارد و در نمایش عمومی استفاده می‌شود.
 */
class Certificate extends Model
{
    /** @use HasFactory<\Database\Factories\CertificateFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'issuer',
        'issued_at',
        'expires_at',
        'credential_url',
        'status',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'issued_at' => 'date',
            'expires_at' => 'date',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublishedEntries(Builder $query): Builder
    {
        return $query->where('status', PublicationStatus::Published->value);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('issued_at');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function periodLabel(): string
    {
        if ($this->issued_at === null) {
            return '';
        }

        $issued = Persian::date($this->issued_at, 'MMMM y');

        return $this->expires_at !== null
            ? $issued.' — '.Persian::date($this->expires_at, 'MMMM y')
            : $issued;
    }
}
