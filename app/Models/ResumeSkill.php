<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SkillCategory;
use App\Models\Concerns\HasActiveOrdering;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumeSkill extends Model
{
    use HasActiveOrdering;
    use HasFactory;

    /** نام جدول صریح — به حدسِ جمع‌سازِ فریم‌ورک تکیه نمی‌کنیم. */
    protected $table = 'resume_skills';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'category' => SkillCategory::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
