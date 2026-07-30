<?php

namespace App\Models;

use Database\Factories\ExpertEngagementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpertEngagement extends Model
{
    /** @use HasFactory<ExpertEngagementFactory> */
    use HasFactory;

    protected $fillable = [
        'expert_profile_id', 'user_key', 'is_saved', 'is_subscribed',
        'last_viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_saved' => 'boolean',
            'is_subscribed' => 'boolean',
            'last_viewed_at' => 'datetime',
        ];
    }

    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }
}
