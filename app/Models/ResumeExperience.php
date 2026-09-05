<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasActiveOrdering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeExperience extends Model
{
    use HasActiveOrdering;
    use HasFactory;

    /** نام جدول صریح — به حدسِ جمع‌سازِ فریم‌ورک تکیه نمی‌کنیم. */
    protected $table = 'resume_experiences';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** بازه‌ی خوانا برای تایم‌لاین — «۱۴۰۰ — اکنون» */
    public function period(): string
    {
        $from = fa_date($this->started_at, 'y');
        $to = $this->ended_at ? fa_date($this->ended_at, 'y') : 'اکنون';

        return "{$from} — {$to}";
    }

    /** دستاوردها به‌صورت خطی — هر خط یک آیتم فهرست. */
    public function achievementLines(): array
    {
        return collect(preg_split('/\R/u', (string) $this->achievements) ?: [])
            ->map(fn (string $line) => trim(ltrim(trim($line), '-*•')))
            ->filter()
            ->values()
            ->all();
    }
}
