<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumReportReasonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ForumReportReason extends Model
{
    /** @use HasFactory<ForumReportReasonFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'translation_key',
        'default_priority',
        'allows_immediate_safety',
        'requires_specialist_review',
        'is_active',
        'position',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'allows_immediate_safety' => 'boolean',
            'requires_specialist_review' => 'boolean',
            'is_active' => 'boolean',
            'position' => 'integer',
            'metadata' => 'array',
        ];
    }
}
