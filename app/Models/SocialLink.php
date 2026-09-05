<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasActiveOrdering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    use HasActiveOrdering;
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * کلید آیکون در اسپرایت خودمیزبان.
     * اگر مدیر کلیدی وارد کند که در اسپرایت نیست، آیکون عمومی «world» رندر می‌شود
     * تا هرگز مربع خالی دیده نشود.
     */
    public function getIconAttribute(): string
    {
        $known = [
            'github' => 'brand-github',
            'linkedin' => 'brand-linkedin',
            'telegram' => 'brand-telegram',
            'twitter' => 'brand-x',
            'x' => 'brand-x',
            'instagram' => 'brand-instagram',
            'youtube' => 'brand-youtube',
            'email' => 'mail',
            'mail' => 'mail',
            'phone' => 'phone',
            'website' => 'world',
        ];

        return $known[strtolower($this->platform)] ?? 'world';
    }
}
