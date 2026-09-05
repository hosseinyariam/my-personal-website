<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasActiveOrdering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeEducation extends Model
{
    use HasActiveOrdering;
    use HasFactory;

    /** نام جدول صریح — به حدسِ جمع‌سازِ فریم‌ورک تکیه نمی‌کنیم. */
    protected $table = 'resume_educations';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'started_year' => 'integer',
            'ended_year' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function period(): string
    {
        $to = $this->ended_year ? fa_digits($this->ended_year) : 'اکنون';

        return fa_digits($this->started_year).' — '.$to;
    }
}
