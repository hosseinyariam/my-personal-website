<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContactPurpose;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'purpose' => ContactPurpose::class,
            'is_read' => 'boolean',
            'mail_sent' => 'boolean',
        ];
    }
}
